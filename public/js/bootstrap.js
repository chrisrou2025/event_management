// assets/bootstrap.js
import { startStimulusApp } from '@symfony/stimulus-bundle';

// Démarre l'application Stimulus
const app = startStimulusApp();

// Désactivé temporairement pour éviter les erreurs de contrôleurs manquants
// import.meta.glob('./controllers/*_controller.js', {
//     eager: true,
// });

console.log('Stimulus app démarré (mode simplifié)');