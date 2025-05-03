package com.syncylinky.services;

import com.syncylinky.models.User;
import org.bytedeco.javacv.*;
import org.bytedeco.opencv.opencv_core.*;
import org.bytedeco.opencv.opencv_face.*;
import org.bytedeco.opencv.opencv_objdetect.*;
import org.bytedeco.javacpp.IntPointer;
import org.bytedeco.javacpp.DoublePointer;
import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardCopyOption;
import java.util.ArrayList;
import java.util.List;

import static org.bytedeco.opencv.global.opencv_core.*;
import static org.bytedeco.opencv.global.opencv_face.*;
import static org.bytedeco.opencv.global.opencv_imgcodecs.*;
import static org.bytedeco.opencv.global.opencv_imgproc.*;
import static org.bytedeco.opencv.global.opencv_objdetect.*;

public class FacialRecognitionService {
    // Utiliser un répertoire utilisateur pour les données persistantes
    private final String FACE_DATA_DIR = System.getProperty("user.home") + File.separator +
            ".syncylinky" + File.separator + "face_data" + File.separator;
    private CascadeClassifier faceDetector;
    private FaceRecognizer faceRecognizer;
    private UserService userService;

    // Constantes pour améliorer la lisibilité et la maintenance
    private static final int FACE_IMAGE_SIZE = 200;
    private static final int REGISTER_FACE_COUNT = 10; // Plus d'images pour une meilleure précision
    private static final int RECOGNITION_ATTEMPTS = 50; // Plus de temps pour la reconnaissance
    private static final double RECOGNITION_THRESHOLD = 55.0; // Seuil de confiance pour la reconnaissance
    private static final double VERIFICATION_THRESHOLD = 65.0; // Seuil de confiance pour la vérification

    public FacialRecognitionService() {
        userService = new UserService();
        initFaceDetector();
        initFaceRecognizer();
    }

    private void initFaceDetector() {
        // Initialiser le détecteur de visage Haar Cascade
        faceDetector = new CascadeClassifier();

        try {
            // Chemin dans les ressources
            String cascadeResourcePath = "/com/syncylinky/haarcascade_frontalface_default.xml";

            // Extraire le fichier depuis les ressources vers un fichier temporaire
            File tempFile = File.createTempFile("cascade", ".xml");
            tempFile.deleteOnExit();

            try (InputStream is = getClass().getResourceAsStream(cascadeResourcePath)) {
                if (is == null) {
                    System.err.println("Le fichier cascade n'a pas été trouvé dans les ressources: " + cascadeResourcePath);
                    return;
                }

                Files.copy(is, tempFile.toPath(), StandardCopyOption.REPLACE_EXISTING);
            }

            // Charger depuis le fichier temporaire
            if (!faceDetector.load(tempFile.getAbsolutePath())) {
                System.err.println("Erreur lors du chargement du fichier cascade");
            } else {
                System.out.println("Fichier cascade chargé avec succès.");
            }
        } catch (IOException e) {
            System.err.println("Erreur lors de l'extraction du fichier cascade: " + e.getMessage());
            e.printStackTrace();
        }

        // Créer le répertoire pour les visages s'il n'existe pas
        new File(FACE_DATA_DIR).mkdirs();
    }

    private void initFaceRecognizer() {
        // Initialiser le reconnaisseur de visage avec des paramètres optimisés
        faceRecognizer = LBPHFaceRecognizer.create(
                1,      // radius - rayon pour le voisinage local
                8,      // neighbors - nombre de points d'échantillonnage
                8,      // grid_x - nombre de cellules en x
                8,      // grid_y - nombre de cellules en y
                100.0   // threshold - valeur par défaut de seuil
        );

        try {
            File modelFile = new File(FACE_DATA_DIR + "face_model.yml");
            if (modelFile.exists()) {
                faceRecognizer.read(modelFile.getAbsolutePath());
                System.out.println("Modèle de reconnaissance faciale chargé depuis: " + modelFile.getAbsolutePath());
            } else {
                // Entraîner avec les visages disponibles si le modèle n'existe pas
                trainRecognizer();
                System.out.println("Aucun modèle existant trouvé, création d'un nouveau modèle de reconnaissance");
            }
        } catch (Exception e) {
            System.err.println("Erreur lors du chargement du modèle de reconnaissance: " + e.getMessage());
        }
    }

    private void trainRecognizer() {
        List<Mat> faces = new ArrayList<>();
        List<Integer> labels = new ArrayList<>();

        // Parcourir le répertoire des visages stockés pour l'entraînement
        File faceDir = new File(FACE_DATA_DIR);
        if (faceDir.exists() && faceDir.isDirectory()) {
            File[] userDirs = faceDir.listFiles();
            if (userDirs != null) {
                for (File userDir : userDirs) {
                    if (userDir.isDirectory()) {
                        try {
                            int userId = Integer.parseInt(userDir.getName());
                            File[] faceFiles = userDir.listFiles();
                            if (faceFiles != null) {
                                for (File faceFile : faceFiles) {
                                    if (faceFile.isFile() && faceFile.getName().endsWith(".jpg")) {
                                        Mat face = imread(faceFile.getAbsolutePath(), IMREAD_GRAYSCALE);
                                        if (!face.empty()) {
                                            // Vérifier que l'image est de la bonne taille
                                            if (face.rows() != FACE_IMAGE_SIZE || face.cols() != FACE_IMAGE_SIZE) {
                                                resize(face, face, new Size(FACE_IMAGE_SIZE, FACE_IMAGE_SIZE));
                                            }
                                            faces.add(face);
                                            labels.add(userId);
                                            System.out.println("Ajout du visage pour l'utilisateur " + userId + ": " + faceFile.getAbsolutePath());
                                        }
                                    }
                                }
                            }
                        } catch (NumberFormatException e) {
                            // Ignorer les répertoires qui ne sont pas des nombres
                            System.out.println("Ignoré répertoire non numérique: " + userDir.getName());
                        }
                    }
                }
            }
        }

        if (!faces.isEmpty()) {
            System.out.println("Entraînement du modèle avec " + faces.size() + " visages");
            MatVector facesMat = new MatVector(faces.size());
            Mat labelsMat = new Mat(labels.size(), 1, CV_32SC1);
            IntPointer labelsPtr = new IntPointer(labelsMat.ptr());

            for (int i = 0; i < faces.size(); i++) {
                facesMat.put(i, faces.get(i));
                labelsPtr.put(i, labels.get(i));
            }

            faceRecognizer.train(facesMat, labelsMat);
            faceRecognizer.save(FACE_DATA_DIR + "face_model.yml");
            System.out.println("Modèle entraîné et sauvegardé avec succès");
        } else {
            System.out.println("Aucun visage trouvé pour l'entraînement");
        }
    }

    /**
     * Enregistre le visage d'un utilisateur avec une interface améliorée
     * @param user L'utilisateur dont on enregistre le visage
     * @return true si l'enregistrement a réussi, false sinon
     */
    public boolean registerFace(User user) {
        try {
            // Créer le répertoire pour l'utilisateur
            String userDir = FACE_DATA_DIR + user.getId() + "/";
            File userDirFile = new File(userDir);
            userDirFile.mkdirs();

            // Supprimer les anciennes images si l'utilisateur met à jour son visage
            if (userDirFile.exists() && userDirFile.isDirectory()) {
                File[] oldFiles = userDirFile.listFiles();
                if (oldFiles != null) {
                    for (File file : oldFiles) {
                        if (file.isFile()) {
                            file.delete();
                        }
                    }
                    System.out.println("Images précédentes supprimées pour l'utilisateur " + user.getId());
                }
            }

            // Configurer le grabber de caméra
            FrameGrabber grabber = new OpenCVFrameGrabber(0);
            grabber.start();

            // Configurer la fenêtre d'affichage
            CanvasFrame frame = new CanvasFrame("Enregistrement du visage", CanvasFrame.getDefaultGamma() / grabber.getGamma());
            frame.setDefaultCloseOperation(javax.swing.JFrame.DISPOSE_ON_CLOSE);

            OpenCVFrameConverter.ToMat converter = new OpenCVFrameConverter.ToMat();

            int capturedFaces = 0;
            final long startTime = System.currentTimeMillis();
            final long timeout = 30000; // 30 secondes maximum pour l'enregistrement

            // Message de début d'enregistrement
            System.out.println("Début de l'enregistrement du visage pour " + user.getName());

            // Capturer plusieurs images du visage pour l'entraînement
            while (frame.isVisible() && capturedFaces < REGISTER_FACE_COUNT &&
                    (System.currentTimeMillis() - startTime) < timeout) {

                Frame capturedFrame = grabber.grab();
                Mat colorImage = converter.convert(capturedFrame);
                Mat grayImage = new Mat();
                cvtColor(colorImage, grayImage, COLOR_BGR2GRAY);

                // Détecter les visages
                RectVector faces = new RectVector();
                faceDetector.detectMultiScale(
                        grayImage,
                        faces,
                        1.1,    // facteur d'échelle
                        3,      // minNeighbors - améliore la qualité de détection
                        0,      // flags (non utilisés)
                        new Size(60, 60), // taille minimale du visage
                        new Size(500, 500) // taille maximale du visage
                );

                if (faces.size() > 0) {
                    // Prendre le plus grand visage (probablement le plus proche)
                    Rect bestFace = getBestFace(faces);

                    // Dessiner un rectangle autour du visage détecté
                    rectangle(colorImage, bestFace, new Scalar(0, 255, 0, 1), 2, LINE_8, 0);

                    // Afficher le compteur de progrès
                    putText(colorImage,
                            "Capture: " + capturedFaces + "/" + REGISTER_FACE_COUNT,
                            new Point(10, 30),
                            FONT_HERSHEY_SIMPLEX, 0.8, new Scalar(0, 255, 0, 1), 2, LINE_AA, false);

                    // Si la taille est suffisante et le visage est bien centré
                    if (bestFace.width() >= 100 && bestFace.height() >= 100) {
                        Mat face = new Mat(grayImage, bestFace);
                        resize(face, face, new Size(FACE_IMAGE_SIZE, FACE_IMAGE_SIZE));

                        // Améliorer la qualité avec une égalisation d'histogramme
                        equalizeHist(face, face);

                        // Sauvegarder l'image à intervalles réguliers
                        if (capturedFaces % 2 == 0) { // Chaque 2 frames pour éviter les doublons
                            String filename = userDir + "face_" + capturedFaces + ".jpg";
                            imwrite(filename, face);
                            capturedFaces++;

                            // Message de confirmation pour chaque visage enregistré
                            System.out.println("Visage " + capturedFaces + "/" + REGISTER_FACE_COUNT +
                                    " capturé pour " + user.getName());
                        }
                    }
                }

                // Afficher l'image avec les informations de progrès
                frame.showImage(converter.convert(colorImage));

                // Petit délai pour permettre la visualisation
                Thread.sleep(100);
            }

            grabber.stop();
            frame.dispose();

            // Vérifier si nous avons capturé assez de visages
            if (capturedFaces < REGISTER_FACE_COUNT / 2) {
                System.out.println("Enregistrement annulé : pas assez d'images capturées pour " + user.getName());
                return false;
            }

            // Réentraîner le modèle avec les nouvelles images
            trainRecognizer();

            // Mettre à jour l'utilisateur pour indiquer qu'il a une identification faciale
            user.setHasFacialRecognition(true);
            userService.updateUser(user);

            // Message final de confirmation
            System.out.println("Visage enregistré avec succès pour " + user.getName() +
                    " (" + capturedFaces + " images capturées)");

            return true;
        } catch (Exception e) {
            System.err.println("Erreur lors de l'enregistrement du visage: " + e.getMessage());
            e.printStackTrace();
            return false;
        }
    }

    /**
     * Sélectionne le meilleur visage dans une liste de rectangles détectés
     * @param faces Collection de rectangles représentant des visages
     * @return Le rectangle du meilleur visage (généralement le plus grand)
     */
    private Rect getBestFace(RectVector faces) {
        Rect bestFace = faces.get(0);
        int maxArea = bestFace.width() * bestFace.height();

        for (int i = 1; i < faces.size(); i++) {
            Rect face = faces.get(i);
            int area = face.width() * face.height();
            if (area > maxArea) {
                maxArea = area;
                bestFace = face;
            }
        }

        return bestFace;
    }

    /**
     * Reconnaît un utilisateur par son visage
     * @return L'utilisateur reconnu ou null si aucun utilisateur n'est reconnu
     * @throws Exception En cas d'erreur lors de la reconnaissance
     */
    public User recognizeFace() throws Exception {
        FrameGrabber grabber = new OpenCVFrameGrabber(0);
        grabber.start();

        CanvasFrame frame = new CanvasFrame("Reconnaissance faciale", CanvasFrame.getDefaultGamma() / grabber.getGamma());
        frame.setDefaultCloseOperation(javax.swing.JFrame.DISPOSE_ON_CLOSE);

        OpenCVFrameConverter.ToMat converter = new OpenCVFrameConverter.ToMat();

        User recognizedUser = null;
        int attempts = 0;
        int consecutiveMatches = 0; // Pour une reconnaissance plus stable
        User lastMatchedUser = null;

        System.out.println("Démarrage de la reconnaissance faciale...");

        while (frame.isVisible() && attempts < RECOGNITION_ATTEMPTS) {
            Frame capturedFrame = grabber.grab();

            Mat colorImage = converter.convert(capturedFrame);
            Mat grayImage = new Mat();
            cvtColor(colorImage, grayImage, COLOR_BGR2GRAY);

            // Détecter les visages avec des paramètres optimisés
            RectVector faces = new RectVector();
            faceDetector.detectMultiScale(
                    grayImage,
                    faces,
                    1.1,    // facteur d'échelle
                    3,      // minNeighbors - plus élevé pour réduire les faux positifs
                    0,      // flags
                    new Size(60, 60),     // taille minimale
                    new Size(500, 500)    // taille maximale
            );

            // Afficher le nombre de tentatives
            putText(colorImage,
                    "Recherche... " + attempts + "/" + RECOGNITION_ATTEMPTS,
                    new Point(10, 30),
                    FONT_HERSHEY_SIMPLEX, 0.7, new Scalar(255, 0, 0, 1), 2, LINE_AA, false);

            if (!faces.empty()) {
                // Prendre le meilleur visage
                Rect bestFace = getBestFace(faces);

                // Dessiner un rectangle autour du visage
                rectangle(colorImage, bestFace, new Scalar(0, 0, 255, 1), 2, LINE_8, 0);

                // Ignorer les visages trop petits pour une bonne précision
                if (bestFace.width() >= 100 && bestFace.height() >= 100) {
                    // Extraire et prétraiter le visage pour la reconnaissance
                    Mat faceROI = new Mat(grayImage, bestFace);
                    resize(faceROI, faceROI, new Size(FACE_IMAGE_SIZE, FACE_IMAGE_SIZE));
                    equalizeHist(faceROI, faceROI); // Améliorer le contraste

                    // Prédire l'identité
                    IntPointer label = new IntPointer(1);
                    DoublePointer confidence = new DoublePointer(1);
                    faceRecognizer.predict(faceROI, label, confidence);

                    int predictedLabel = label.get(0);
                    double conf = confidence.get(0);

                    // Afficher le score de confiance
                    putText(colorImage,
                            String.format("Confiance: %.1f", conf),
                            new Point(bestFace.x(), bestFace.y() - 10),
                            FONT_HERSHEY_SIMPLEX, 0.6, new Scalar(0, 0, 255, 1), 2, LINE_AA, false);

                    // Si la confiance est assez haute (valeur plus basse = plus confiant)
                    if (conf < RECOGNITION_THRESHOLD) {
                        User user = userService.getUserById(predictedLabel);
                        if (user != null) {
                            // Vérifier si c'est le même utilisateur que la détection précédente
                            if (lastMatchedUser != null && lastMatchedUser.getId() == user.getId()) {
                                consecutiveMatches++;
                            } else {
                                consecutiveMatches = 1;
                                lastMatchedUser = user;
                            }

                            // Afficher le nom de l'utilisateur reconnu
                            putText(colorImage,
                                    user.getName(),
                                    new Point(bestFace.x(), bestFace.y() - 30),
                                    FONT_HERSHEY_SIMPLEX, 0.8, new Scalar(0, 255, 0, 1), 2, LINE_AA, false);

                            // Si nous avons 3 détections consécutives du même utilisateur, c'est confirmé
                            if (consecutiveMatches >= 3) {
                                recognizedUser = user;
                                // Afficher confirmation visuelle
                                rectangle(colorImage, bestFace, new Scalar(0, 255, 0, 1), 3, LINE_8, 0);
                                putText(colorImage, "IDENTIFIÉ: " + user.getName(),
                                        new Point(10, colorImage.rows() - 20),
                                        FONT_HERSHEY_SIMPLEX, 0.8, new Scalar(0, 255, 0, 1), 2, LINE_AA, false);

                                // Attendre un moment pour que l'utilisateur voie la confirmation
                                frame.showImage(converter.convert(colorImage));
                                Thread.sleep(1500);
                                break;
                            }
                        }
                    }
                }
            }

            // Afficher l'image avec les rectangles et les noms
            frame.showImage(converter.convert(colorImage));
            attempts++;

            // Petit délai pour réduire la charge CPU
            Thread.sleep(50);
        }

        grabber.stop();
        frame.dispose();

        if (recognizedUser != null) {
            System.out.println("Utilisateur reconnu: " + recognizedUser.getName());
        } else {
            System.out.println("Aucun utilisateur reconnu après " + attempts + " tentatives");
        }

        return recognizedUser;
    }

    /**
     * Vérifie si le visage détecté correspond à l'utilisateur spécifié
     * @param user L'utilisateur à vérifier
     * @return true si le visage correspond à l'utilisateur, false sinon
     * @throws Exception En cas d'erreur lors de la vérification
     */
    public boolean verifyUserFace(User user) throws Exception {
        if (user == null) {
            System.err.println("Erreur: utilisateur null fourni pour la vérification faciale");
            return false;
        }

        FrameGrabber grabber = new OpenCVFrameGrabber(0);
        grabber.start();

        CanvasFrame frame = new CanvasFrame("Vérification du visage de " + user.getName(),
                CanvasFrame.getDefaultGamma() / grabber.getGamma());
        frame.setDefaultCloseOperation(javax.swing.JFrame.DISPOSE_ON_CLOSE);

        OpenCVFrameConverter.ToMat converter = new OpenCVFrameConverter.ToMat();

        boolean isVerified = false;
        int attempts = 0;
        int consecutiveMatches = 0;
        final int requiredMatches = 3; // Nombre de correspondances consécutives requises

        System.out.println("Démarrage de la vérification faciale pour " + user.getName());

        while (frame.isVisible() && attempts < RECOGNITION_ATTEMPTS && !isVerified) {
            Frame capturedFrame = grabber.grab();

            Mat colorImage = converter.convert(capturedFrame);
            Mat grayImage = new Mat();
            cvtColor(colorImage, grayImage, COLOR_BGR2GRAY);

            // Détecter les visages
            RectVector faces = new RectVector();
            faceDetector.detectMultiScale(
                    grayImage,
                    faces,
                    1.1,  // facteur d'échelle
                    3,    // minNeighbors
                    0,    // flags
                    new Size(60, 60),  // taille minimale
                    new Size(500, 500) // taille maximale
            );

            // Afficher message d'instruction
            putText(colorImage,
                    "Regardez la caméra - Vérification: " + attempts + "/" + RECOGNITION_ATTEMPTS,
                    new Point(10, 30),
                    FONT_HERSHEY_SIMPLEX, 0.7, new Scalar(255, 0, 0, 1), 2, LINE_AA, false);

            if (!faces.empty()) {
                // Prendre le meilleur visage
                Rect bestFace = getBestFace(faces);

                // Ignorer les visages trop petits
                if (bestFace.width() >= 100 && bestFace.height() >= 100) {
                    // Dessiner un rectangle autour du visage
                    rectangle(colorImage, bestFace, new Scalar(0, 0, 255, 1), 2, LINE_8, 0);

                    Mat faceROI = new Mat(grayImage, bestFace);
                    resize(faceROI, faceROI, new Size(FACE_IMAGE_SIZE, FACE_IMAGE_SIZE));
                    equalizeHist(faceROI, faceROI);

                    IntPointer label = new IntPointer(1);
                    DoublePointer confidence = new DoublePointer(1);
                    faceRecognizer.predict(faceROI, label, confidence);

                    int predictedLabel = label.get(0);
                    double conf = confidence.get(0);

                    // Afficher le score de confiance
                    putText(colorImage,
                            String.format("Confiance: %.1f", conf),
                            new Point(bestFace.x(), bestFace.y() - 10),
                            FONT_HERSHEY_SIMPLEX, 0.6, new Scalar(0, 0, 255, 1), 2, LINE_AA, false);

                    // Vérifier si c'est l'utilisateur attendu avec une bonne confiance
                    if (predictedLabel == user.getId() && conf < VERIFICATION_THRESHOLD) {
                        consecutiveMatches++;

                        // Afficher le nom avec une indicateur de correspondance
                        putText(colorImage,
                                user.getName() + " ✓",
                                new Point(bestFace.x(), bestFace.y() - 30),
                                FONT_HERSHEY_SIMPLEX, 0.8, new Scalar(0, 255, 0, 1), 2, LINE_AA, false);

                        // Rectangle vert si correspondance
                        rectangle(colorImage, bestFace, new Scalar(0, 255, 0, 1), 2, LINE_8, 0);

                        // Si nous avons assez de correspondances consécutives
                        if (consecutiveMatches >= requiredMatches) {
                            isVerified = true;
                            // Message de confirmation
                            rectangle(colorImage, bestFace, new Scalar(0, 255, 0, 1), 3, LINE_8, 0);
                            putText(colorImage,
                                    "IDENTITÉ CONFIRMÉE: " + user.getName(),
                                    new Point(10, colorImage.rows() - 20),
                                    FONT_HERSHEY_SIMPLEX, 0.8,
                                    new Scalar(0, 255, 0, 1), 2, LINE_AA, false);
                        }
                    } else {
                        // Réinitialiser le compteur de correspondances en cas d'échec
                        consecutiveMatches = 0;
                    }
                }
            }

            frame.showImage(converter.convert(colorImage));
            attempts++;

            // Petit délai
            Thread.sleep(50);

            // Si vérifié, attendre un moment pour afficher le message de confirmation
            if (isVerified) {
                Thread.sleep(1500);
            }
        }

        grabber.stop();
        frame.dispose();

        if (isVerified) {
            System.out.println("Identité de " + user.getName() + " vérifiée avec succès");
        } else {
            System.out.println("Échec de la vérification pour " + user.getName() + " après " + attempts + " tentatives");
        }

        return isVerified;
    }
}