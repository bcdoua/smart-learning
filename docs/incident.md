# Compte rendu d'incident : mise en service de v1.1.0

- **Ce qui s'est passé** : la version v1.1.0, publiée volontairement avec une route /health qui lève une exception, a été mise en service en staging. Le test /health était désactivé, donc le pipeline CI est resté vert.
- **Quand nous l'avons su** : à 13:27, le contrôle de santé (scripts/wait-healthy.sh) a reçu des erreurs 500 pendant 1 min 11 s et a renvoyé « staging non sain » (code 1). Les journaux indiquent la première erreur : RuntimeException à HealthController.php, ligne 14.
- **Ce que nous avons fait** : retour arrière avec ./scripts/deploy.sh v1.0.0, en réutilisant l'image déjà publiée et validée, sans aucune reconstruction. Le contrôle de santé est repassé au vert (code 0).
- **Durée d'indisponibilité** : environ 2 minutes (13:26:21 à 13:28:23). Le retour arrière lui-même a pris 14 secondes.
- **Prévention** : ne jamais désactiver un test pour faire passer une version, ajouter un contrôle de santé automatique après chaque mise en service (bonus : déploiement sur tag), et ne jamais taguer avant la fusion de la PR (un tag v1.1.0 a d'abord été posé sur le mauvais commit, puis supprimé et recréé).
