<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeautyBar Amal</title>
    <script src="controls.js"></script>
    <link rel="stylesheet" href="style_util.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   
    <link rel="stylesheet" href="https//fonts.googleapis.com/css?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap">
    <!--using-font-awesome----------->
    <script src="https://kit.fontawesome.com/c8e4d183c2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400..700;1,400..700&family=Playwrite+DE+LA+Guides&display=swap" rel="stylesheet">
<script>
  document.addEventListener('DOMContentLoaded', function() {
  const container = document.getElementById('authContainer');
  const showSignup = document.getElementById('showSignup');
  const showLogin = document.getElementById('showLogin');
  const loginForm = document.getElementById('loginForm');
  const signupForm = document.getElementById('signupForm');

  // Ces boutons n'existent plus une fois l'utilisateur connecté,
  // donc on vérifie qu'ils sont bien présents avant d'ajouter les écouteurs.
  if (showSignup && loginForm && signupForm && container) {
    showSignup.addEventListener('click', function(e) {
      e.preventDefault();
      container.classList.add('flipped');
      loginForm.style.display = 'none';
      signupForm.style.display = 'flex';
    });
  }

  if (showLogin && loginForm && signupForm && container) {
    showLogin.addEventListener('click', function(e) {
      e.preventDefault();
      container.classList.remove('flipped');
      signupForm.style.display = 'none';
      loginForm.style.display = 'flex';
    });
  }

  // Bouton "Se connecter" dans la section Avis :
  // on scrolle en haut ET on force l'ouverture du menu de connexion.
  const btnAllerConnexion = document.getElementById('btnAllerConnexion');
  const profileWrapper = document.querySelector('.nav-icons .profile-wrapper');

  if (btnAllerConnexion && profileWrapper) {
    btnAllerConnexion.addEventListener('click', function (e) {
      e.preventDefault();
      document.getElementById('home').scrollIntoView({ behavior: 'smooth' });
      profileWrapper.classList.add('force-open');

      // On referme automatiquement si l'utilisateur clique ailleurs
      setTimeout(function () {
        document.addEventListener('click', function fermerMenu(ev) {
          if (!profileWrapper.contains(ev.target)) {
            profileWrapper.classList.remove('force-open');
            document.removeEventListener('click', fermerMenu);
          }
        });
      }, 100);
    });
  }

  // Même comportement pour le bouton "Se connecter" de la section Rendez-vous
  const btnAllerConnexionRdv = document.getElementById('btnAllerConnexionRdv');
  if (btnAllerConnexionRdv && profileWrapper) {
    btnAllerConnexionRdv.addEventListener('click', function (e) {
      e.preventDefault();
      document.getElementById('home').scrollIntoView({ behavior: 'smooth' });
      profileWrapper.classList.add('force-open');
      setTimeout(function () {
        document.addEventListener('click', function fermerMenu2(ev) {
          if (!profileWrapper.contains(ev.target)) {
            profileWrapper.classList.remove('force-open');
            document.removeEventListener('click', fermerMenu2);
          }
        });
      }, 100);
    });
  }

  // Notification de mise à jour de rendez-vous (pastille rouge sur l'icône profil)
  const profileWrapperConnecte = document.getElementById('profileWrapperConnecte');
  const pastilleNotif = document.getElementById('pastilleNotif');
  if (profileWrapperConnecte && pastilleNotif) {
    let dejaMarque = false;
    profileWrapperConnecte.addEventListener('mouseenter', function () {
      if (dejaMarque) return;
      dejaMarque = true;
      pastilleNotif.style.display = 'none';
      fetch('marquer_rdv_vu_client.php').catch(() => {});
    });
  }
});
</script>

<!-- Calendrier des rendez-vous -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const grille = document.getElementById('rdvGrille');
  if (!grille) return; // section rendez-vous absente de cette page

  const moisAffiche = document.getElementById('moisAffiche');
  const creneauxJourDiv = document.getElementById('rdvCreneauxJour');
  const btnPrecedent = document.getElementById('moisPrecedent');
  const btnSuivant = document.getElementById('moisSuivant');
  const inputDate = document.getElementById('rdvDate');
  const inputHeure = document.getElementById('rdvHeure');
  const avertissement = document.getElementById('rdvAvertissement');
  const boutonConfirmer = document.getElementById('rdvSubmitBtn');

  const noms = ["Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Août","Septembre","Octobre","Novembre","Décembre"];

  // Regrouper les créneaux occupés par jour : { "2026-09-01": ["14:00", "16:30"] }
  const occupesParJour = {};
  creneauxOccupes.forEach(function (iso) {
    const jour = iso.substring(0, 10);
    const heure = iso.substring(11, 16);
    if (!occupesParJour[jour]) occupesParJour[jour] = [];
    occupesParJour[jour].push(heure);
  });

  let dateAffichee = new Date();
  dateAffichee.setDate(1);

  function pad(n) { return n < 10 ? '0' + n : n; }

  function dessinerCalendrier() {
    const annee = dateAffichee.getFullYear();
    const mois = dateAffichee.getMonth();
    moisAffiche.textContent = noms[mois] + ' ' + annee;

    grille.innerHTML = '';

    const premierJourMois = new Date(annee, mois, 1);
    // getDay() : 0=dimanche ... 6=samedi -> on veut lundi en premier
    let decalage = premierJourMois.getDay() - 1;
    if (decalage < 0) decalage = 6;

    const nbJours = new Date(annee, mois + 1, 0).getDate();
    const aujourdHui = new Date();
    aujourdHui.setHours(0,0,0,0);

    for (let i = 0; i < decalage; i++) {
      const vide = document.createElement('div');
      vide.className = 'rdv-jour rdv-jour-vide';
      grille.appendChild(vide);
    }

    for (let j = 1; j <= nbJours; j++) {
      const cle = annee + '-' + pad(mois + 1) + '-' + pad(j);
      const cellule = document.createElement('div');
      cellule.className = 'rdv-jour';
      cellule.textContent = j;

      const dateCellule = new Date(annee, mois, j);
      if (dateCellule < aujourdHui) {
        cellule.classList.add('rdv-jour-passe');
      } else if (occupesParJour[cle]) {
        cellule.classList.add('rdv-jour-occupe');
      }

      cellule.addEventListener('click', function () {
        document.querySelectorAll('.rdv-jour.selectionne').forEach(el => el.classList.remove('selectionne'));
        cellule.classList.add('selectionne');
        afficherCreneaux(cle);
        if (inputDate) inputDate.value = cle;
      });

      grille.appendChild(cellule);
    }
  }

  function afficherCreneaux(cle) {
    const heures = occupesParJour[cle];
    if (!heures || heures.length === 0) {
      creneauxJourDiv.innerHTML = '<p class="rdv-hint">Aucun créneau réservé ce jour-là — journée libre 🎉</p>';
      return;
    }
    let html = '<p class="rdv-hint">Créneaux déjà réservés le ' + cle.split('-').reverse().join('/') + ' :</p><ul class="rdv-liste-heures">';
    heures.sort().forEach(function (h) {
      html += '<li>' + h + '</li>';
    });
    html += '</ul>';
    creneauxJourDiv.innerHTML = html;
  }

  // Avertit en direct si le créneau choisi dans le formulaire est déjà pris,
  // et empêche carrément d'envoyer le formulaire dans ce cas.
  function differenceMinutes(h1, h2) {
    const [a1, a2] = h1.split(':').map(Number);
    const [b1, b2] = h2.split(':').map(Number);
    return Math.abs((a1 * 60 + a2) - (b1 * 60 + b2));
  }

  function verifierCreneauChoisi() {
    if (!inputDate || !inputHeure || !avertissement) return;
    const cle = inputDate.value;
    const heure = inputHeure.value;
    const horsHoraires = heure && (heure < "10:00" || heure > "20:00");

    // Il faut au moins 1h30 (90 min) entre deux rendez-vous, pas juste éviter l'heure exacte
    let tropProche = false;
    if (cle && heure && occupesParJour[cle]) {
      tropProche = occupesParJour[cle].some(h => differenceMinutes(h, heure) < 90);
    }

    if (horsHoraires) {
      avertissement.textContent = "⚠️ BeautyBar est ouvert de 10h00 à 20h00, merci de choisir une heure dans cette plage.";
      if (boutonConfirmer) boutonConfirmer.disabled = true;
    } else if (tropProche) {
      avertissement.textContent = "⚠️ Il faut au moins 1h30 entre deux rendez-vous, choisis une autre heure.";
      if (boutonConfirmer) boutonConfirmer.disabled = true;
    } else {
      avertissement.textContent = "";
      if (boutonConfirmer) boutonConfirmer.disabled = false;
    }
  }

  if (inputDate) inputDate.addEventListener('change', verifierCreneauChoisi);
  if (inputHeure) inputHeure.addEventListener('change', verifierCreneauChoisi);

  // Calcul du total en direct selon les services cochés
  const casesServices = document.querySelectorAll('#rdvServicesChoix input[type="checkbox"]');
  const totalAffiche = document.getElementById('rdvTotalPrix');
  if (casesServices.length > 0 && totalAffiche) {
    function recalculerTotal() {
      let total = 0;
      casesServices.forEach(c => {
        if (c.checked) total += parseInt(c.dataset.prix, 10) || 0;
      });
      totalAffiche.textContent = total + ' DT';
    }
    casesServices.forEach(c => c.addEventListener('change', recalculerTotal));
  }

  if (btnPrecedent) {
    btnPrecedent.addEventListener('click', function () {
      dateAffichee.setMonth(dateAffichee.getMonth() - 1);
      dessinerCalendrier();
    });
  }
  if (btnSuivant) {
    btnSuivant.addEventListener('click', function () {
      dateAffichee.setMonth(dateAffichee.getMonth() + 1);
      dessinerCalendrier();
    });
  }

  dessinerCalendrier();
});
</script>
<!-- Notation par étoiles pour le formulaire d'avis -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const etoilesChoix = document.getElementById('etoilesChoix');
    const noteInput = document.getElementById('noteChoisie');
    if (!etoilesChoix || !noteInput) return; // formulaire absent si pas connecté

    const etoiles = etoilesChoix.querySelectorAll('span');
    etoiles.forEach(etoile => {
      etoile.addEventListener('click', function () {
        const valeur = parseInt(this.getAttribute('data-note'));
        noteInput.value = valeur;
        etoiles.forEach(e => {
          e.classList.toggle('active', parseInt(e.getAttribute('data-note')) <= valeur);
        });
      });
    });

    const avisForm = document.getElementById('avisForm');
    if (avisForm) {
      avisForm.addEventListener('submit', function (e) {
        if (parseInt(noteInput.value) < 1) {
          e.preventDefault();
          alert('Merci de choisir une note avant de publier votre avis.');
        }
      });
    }
  });
</script>

</head>
<body>


  <section id="home">
    <fieldset >
      <nav class="navbar"> 
        <div class="logo"><img src="logo.png" alt=""></div>   
        <div class="nav-links">
          <a href="#home">Acceuil</a>
          <a href="#about">à propos</a>
          <a href="#products">Nos services</a>
          <a href="#review">Avis</a>
          <a href="#rendezvous">Rendez-vous</a>
          <a href="#container">Contacter</a>
        </div>
        <div class="nav-icons">
          <?php if (isset($_SESSION["connecte"]) && $_SESSION["connecte"] === true):

            // On vérifie si le client a une mise à jour de rendez-vous non consultée
            $cNotif = getConnexionDB();
            $npNotifEchap = mysqli_real_escape_string($cNotif, $_SESSION["nom"]);
            $resNotif = mysqli_query($cNotif, "SELECT statut FROM rendez_vous WHERE np='$npNotifEchap' AND vu_client = 0");
            $notifRdv = $resNotif ? mysqli_fetch_assoc($resNotif) : null;
            mysqli_close($cNotif);

            $messagesNotif = [
                'confirme' => '✅ Ton rendez-vous a été confirmé !',
                'refuse'   => '❌ Ton rendez-vous a été refusé.',
            ];
          ?>

          <!-- Etat connecté : menu déroulant au survol, comme le reste du site -->
          <div class="profile-wrapper" id="profileWrapperConnecte">
            <span class="profile-icon-zone">
              <i class="fa-solid fa-user profile-icon"></i>
              <?php if ($notifRdv): ?><span class="pastille-notif" id="pastilleNotif"></span><?php endif; ?>
            </span>
            <div class="user-menu">
              <span class="user-connecte">Bonjour, <?php echo htmlspecialchars($_SESSION["nom"]); ?></span>
              <?php if ($notifRdv): ?>
                <a href="#rendezvous" class="notif-rdv-lien" id="notifRdvLien">
                  <?php echo $messagesNotif[$notifRdv['statut']] ?? 'Mise à jour de ton rendez-vous.'; ?>
                </a>
              <?php endif; ?>
              <a href="deconnexion.php" class="btn-deconnexion">Se déconnecter</a>
            </div>
          </div>

          <?php else: ?>

          <div class="profile-wrapper<?php echo isset($_GET['erreur_connexion']) ? ' force-open' : ''; ?>">
            <span class="profile-icon"><i class="fa-solid fa-user profile-icon"></i></span>

            <!-- Ton auth-container, maintenant caché par défaut -->
            <div class="auth-container" id="authContainer">

              <!-- Photo -->
              <div class="auth-photo" id="authPhoto">
                <img src="img.jpg" alt="Beauty Bar">
              </div>

              <!-- Zone formulaires -->
              <div class="auth-forms" id="authForms">

                <form class="form-login" id="loginForm" action="connecter.php" method="post" onsubmit="return connecte() ">
                  <h3>Connexion</h3>
                  <input type="email" name="email" placeholder="Email" required>
                  <input type="password" name="mp" placeholder="Mot de passe" required>
                  <?php if (isset($_GET['erreur_connexion'])): ?>
                    <p class="erreur-champ">Email ou mot de passe incorrect.</p>
                  <?php endif; ?>
                  <button type="submit">Se connecter</button>
                  <p>Pas encore de compte ?
                    <a href="#" id="showSignup">Créer un nouveau compte</a>
                  </p>
                </form>

                <form class="form-signup" id="signupForm" style="display: none;" action="inscrire.php" method="post" onsubmit="return inscrie()">
                  <h3>Créer un compte</h3>
                  <input type="text" name="np" id="np" placeholder="Nom complet" required>
                  <input type="email" name="email" id="email" placeholder="Email" required>
                  <input type="password" name="mp" id="mp" placeholder="Mot de passe" required>
                  <button type="submit">S'inscrire</button>
                  <p id="messageInscription"></p>
                  <p>Déjà un compte ?
                    <a href="#" id="showLogin">Se connecter</a>
                  </p>
                </form>

              </div>
            </div>
          </div>

          <?php endif; ?>
        </div>
      </nav>
     <div id="imgCarousel" class="carousel slide" data-bs-ride="carousel">

  <div id="imgCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="2000">

  <!-- Indicateurs (points en bas) -->
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#imgCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
    <button type="button" data-bs-target="#imgCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button type="button" data-bs-target="#imgCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
  </div>

  <div class="carousel-inner">
    <div class="carousel-item active" data-bs-interval="2000">
      <img src="modele1.jpg" class="d-block" alt="Modèle 1">
      <div class="carousel-caption d-none d-md-block">
        <h5>Manucure élégante</h5>
        <p>Un style raffiné pour sublimer vos mains au quotidien.</p>
      </div>
    </div>

    <div class="carousel-item" data-bs-interval="2000">
      <img src="modele2.jpg" class="d-block" alt="Modèle 2">
      <div class="carousel-caption d-none d-md-block">
        <h5>Nail art personnalisé</h5>
        <p>Des créations uniques adaptées à vos envies.</p>
      </div>
    </div>

    <div class="carousel-item" data-bs-interval="2000">
      <img src="modele3.jpg" class="d-block" alt="Modèle 3">
      <div class="carousel-caption d-none d-md-block">
        <h5>Finition brillante</h5>
        <p>Un rendu impeccable et durable pour toutes les occasions.</p>
      </div>
    </div>
  </div>

  <!-- Contrôles -->
  <button class="carousel-control-prev" type="button" data-bs-target="#imgCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#imgCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>

<div class="home-text">
  <h1>BEAUTYBAR.AMALR'H</h1>
  <h2>La beauté jusqu'au bout des ongles ❤️</h2>
  <a href="#contact">
    <button id="btn1">Contactez-nous</button>
  </a>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </fieldset>
  </section>

  <section id="about">
      <h2 >A propos</h2>
      <p >
      Bienvenue chez <strong>Beauty Bar by Amal</strong> , votre espace beauté à Bouhjar.
       Nous vous accueillons dans une ambiance chaleureuse et élégante, où chaque détail est pensé pour vous 
       offrir un moment de détente et de bien-être. Passionnés par la beauté et soucieux de votre satisfaction,
      nous mettons notre savoir-faire et notre attention au service de vos envies pour vous faire sentir belle
       et confiante. ✨
      <br><br>
      </p>
  </section>

   
  <section id="products" class="py-5" style="background-color: #fff0f5;">
  <div class="container">
    <h2 class="text-center text-danger mb-5">Nos services les plus populaires</h2>
    <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">

        <!-- Slide 1 -->
        <div class="carousel-item active" data-bs-interval="2000">
          <div class="row">
            <div class="product-card" >
              <img src="photo1.png" alt="photo1">
            </div>
            <div class="product-card">
              <img src="photo2.png" alt="photo2">
            </div>
          </div>
        </div>

        <!-- Slide 2 -->
        <div class="carousel-item" data-bs-interval="2000">
          <div class="row">
            <div class="product-card">
              <img src="photo3.png" alt="photo3">
            </div>
            <div class="product-card">
              <img src="photo4.png" alt="photo4">
            </div>
          </div>
        </div>

        <!-- Slide 3 -->
        <div class="carousel-item" data-bs-interval="2000">
          <div class="row">
            <div class="product-card">
              <img src="photo5.png" alt="photo5">
            </div>
            <div class="product-card">
              <img src="photo6.png" alt="photo6">
            </div>
          </div>
        </div>
        <!-- Slide 4 -->
        <div class="carousel-item" data-bs-interval="2000">
          <div class="row">
            <div class="product-card">
              <img src="photo7.png" alt="photo7">
            </div>
            <div class="product-card">
              <img src="photo8.png" alt="photo8">
            </div>
          </div>
        </div>

      </div>

      <!-- Controls -->
      <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </div>
</section>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Zone Rendez-vous -->
<section id="rendezvous" class="rdv-section">
  <h2>Prendre rendez-vous</h2>

  <div class="rdv-wrap">

    <!-- Calendrier : affiche seulement quels créneaux sont déjà pris, pas le nom ni le service -->
    <div class="rdv-calendrier">
      <div class="rdv-calendrier-header">
        <button type="button" id="moisPrecedent">&#8592;</button>
        <h3 id="moisAffiche"></h3>
        <button type="button" id="moisSuivant">&#8594;</button>
      </div>
      <div class="rdv-jours-entete">
        <span>Lun</span><span>Mar</span><span>Mer</span><span>Jeu</span><span>Ven</span><span>Sam</span><span>Dim</span>
      </div>
      <div class="rdv-grille" id="rdvGrille"></div>
      <div class="rdv-creneaux-jour" id="rdvCreneauxJour">
        <p class="rdv-hint">Clique sur une date pour voir les créneaux déjà réservés.</p>
      </div>
    </div>

    <!-- Formulaire de réservation -->
    <div class="rdv-form-zone">
      <?php if (isset($_SESSION["connecte"]) && $_SESSION["connecte"] === true):

        // On récupère tous les rendez-vous à venir de ce client (plusieurs possibles)
        $cRdv = getConnexionDB();
        $npEchap = mysqli_real_escape_string($cRdv, $_SESSION["nom"]);
        $reqExistant = "SELECT id, date, num, statut FROM rendez_vous WHERE np='$npEchap' AND date >= NOW() ORDER BY date ASC";
        $resExistant = mysqli_query($cRdv, $reqExistant);
      ?>

        <div class="rdv-form-container">
          <h3>Réserver un créneau</h3>
          <p class="connecte-comme">Connecté en tant que <strong><?php echo htmlspecialchars($_SESSION["nom"]); ?></strong></p>

          <?php if ($resExistant && mysqli_num_rows($resExistant) > 0):
            $statutClasses = [
                'en_attente' => 'rdv-statut-attente',
                'confirme'   => 'rdv-statut-confirme',
                'refuse'     => 'rdv-statut-refuse',
            ];
            $statutTextes = [
                'en_attente' => '⏳ En attente de confirmation par notre équipe.',
                'confirme'   => '✅ Rendez-vous confirmé !',
                'refuse'     => '❌ Ce rendez-vous a été refusé.',
            ];
            while ($rdv = mysqli_fetch_assoc($resExistant)):
                // Services rattachés à ce rendez-vous
                $idRdvEchap = (int)$rdv['id'];
                $resServicesRdv = mysqli_query($cRdv, "SELECT service FROM rendez_vous_services WHERE id_rdv = $idRdvEchap");
                $servicesListe = [];
                while ($s = mysqli_fetch_assoc($resServicesRdv)) { $servicesListe[] = $s['service']; }
                $statutActuel = $rdv['statut'];
          ?>
            <div class="rdv-existant">
              <p>Ton rendez-vous :</p>
              <p class="rdv-existant-detail">
                <strong><?php echo htmlspecialchars(implode(' + ', $servicesListe)); ?></strong> —
                <?php echo date("d/m/Y à H:i", strtotime($rdv["date"])); ?>
              </p>
              <p class="rdv-statut <?php echo $statutClasses[$statutActuel] ?? ''; ?>">
                <?php echo $statutTextes[$statutActuel] ?? ''; ?>
              </p>
              <form action="annuler_rdv.php" method="post" style="margin-top:10px;">
                <input type="hidden" name="id" value="<?php echo $idRdvEchap; ?>">
                <button type="submit" class="btn-annuler-rdv">Annuler ce rendez-vous</button>
              </form>
            </div>
          <?php endwhile; endif; ?>

          <form id="rdvForm" action="prendre_rdv.php" method="post">
            <label>Services souhaités <span class="note-info">(un ou plusieurs)</span></label>
            <div class="rdv-services-choix" id="rdvServicesChoix">
              <?php
              $resServices = mysqli_query($cRdv, "SELECT service, prix FROM les_services ORDER BY service ASC");
              while ($s = mysqli_fetch_assoc($resServices)) {
                  $sid = 'srv_' . md5($s['service']);
                  echo '<label class="rdv-service-item" for="' . $sid . '">';
                  echo '<input type="checkbox" name="services[]" id="' . $sid . '" value="' . htmlspecialchars($s['service']) . '" data-prix="' . (int)$s['prix'] . '">';
                  echo '<span>' . htmlspecialchars($s['service']) . '</span>';
                  echo '<span class="rdv-service-prix">' . (int)$s['prix'] . ' DT</span>';
                  echo '</label>';
              }
              ?>
            </div>
            <p class="rdv-total">Total : <strong id="rdvTotalPrix">0 DT</strong></p>

            <label for="rdvTelephone">Numéro de téléphone</label>
            <input type="tel" name="num" id="rdvTelephone" placeholder="Ex: 25913424" required>

            <label for="rdvDate">Date souhaitée</label>
            <input type="date" name="date_rdv" id="rdvDate" required>

            <label for="rdvHeure">Heure souhaitée</label>
            <input type="time" name="heure_rdv" id="rdvHeure" min="10:00" max="20:00" required>
            <p class="note-info">BeautyBar est ouvert de 10h00 à 20h00.</p>

            <?php
            $messagesErreurRdv = [
                'conflit'   => "Il faut au moins 1h30 entre deux rendez-vous. Merci d'en choisir un autre.",
                'passee'    => "Impossible de réserver une date déjà passée.",
                'champs'    => "Merci de remplir tous les champs et choisir au moins un service.",
                'service'   => "Un des services choisis est inconnu.",
                'horaire'   => "BeautyBar est ouvert de 10h00 à 20h00. Merci de choisir une heure dans cette plage.",
                'meme_jour' => "Tu as déjà un rendez-vous ce jour-là. Un seul rendez-vous par jour est autorisé.",
            ];
            if (isset($_GET['erreur_rdv']) && isset($messagesErreurRdv[$_GET['erreur_rdv']])):
            ?>
              <p class="erreur-champ"><?php echo $messagesErreurRdv[$_GET['erreur_rdv']]; ?></p>
            <?php endif; ?>

            <p class="rdv-avertissement" id="rdvAvertissement"></p>

            <button type="submit" id="rdvSubmitBtn">Confirmer la demande</button>
            <p class="note-info">Ta demande sera confirmée par notre équipe.</p>
          </form>
          <?php mysqli_close($cRdv); ?>
        </div>

      <?php else: ?>

        <div class="rdv-connexion-requise">
          <p>Tu dois être connecté(e) pour prendre rendez-vous.</p>
          <button type="button" id="btnAllerConnexionRdv">Se connecter</button>
        </div>

      <?php endif; ?>
    </div>

  </div>
</section>

<!-- Données des créneaux déjà occupés, pour le calendrier (dates/heures seulement, rien d'autre) -->
<script>
  const creneauxOccupes = [
    <?php
    $cCal = getConnexionDB();
    $resCal = mysqli_query($cCal, "SELECT date FROM rendez_vous WHERE date >= NOW()");
    $dates = [];
    while ($row = mysqli_fetch_assoc($resCal)) {
        $dates[] = "'" . date("Y-m-d\TH:i:00", strtotime($row["date"])) . "'";
    }
    mysqli_close($cCal);
    echo implode(",\n    ", $dates);
    ?>
  ];
</script>

  <!-- Zone avis -->
<section class="avis-section" id="review">
  <h2>Ce que nos clients disent</h2>

  <div class="avis-liste" id="avisListe">
    <?php
    $cAvis = getConnexionDB();
    $reqAvis = "SELECT nom, texte, note FROM avis WHERE statut='approuve' ORDER BY date_creation DESC";
    $resAvis = mysqli_query($cAvis, $reqAvis);

    if (!$resAvis || mysqli_num_rows($resAvis) === 0) {
        echo '<p>Aucun avis pour le moment. Soyez le premier !</p>';
    } else {
        while ($avis = mysqli_fetch_assoc($resAvis)) {
            echo '<div class="avis-card">';
            echo '  <div class="avis-header">';
            echo '    <div>';
            echo '      <h4>' . htmlspecialchars($avis['nom']) . '</h4>';
            echo '      <div class="etoiles">' . str_repeat('⭐', (int)$avis['note']) . '</div>';
            echo '    </div>';
            echo '  </div>';
            echo '  <p class="avis-texte">' . htmlspecialchars($avis['texte']) . '</p>';
            echo '</div>';
        }
    }
    mysqli_close($cAvis);
    ?>
  </div>

  <?php if (isset($_SESSION["connecte"]) && $_SESSION["connecte"] === true): ?>

  <!-- Affiché SEULEMENT si connecté -->
  <div class="avis-form-container" id="avisFormContainer">
    <h3>Laissez votre avis</h3>
    <p class="connecte-comme">Connecté en tant que <strong><?php echo htmlspecialchars($_SESSION["nom"]); ?></strong></p>
    <form id="avisForm" action="publier_avis.php" method="post">
      <div class="etoiles-choix" id="etoilesChoix">
        <span data-note="1">⭐</span><span data-note="2">⭐</span><span data-note="3">⭐</span><span data-note="4">⭐</span><span data-note="5">⭐</span>
      </div>
      <input type="hidden" name="note" id="noteChoisie" value="0">
      <textarea name="texte" id="avisTexte" placeholder="Écrivez votre avis ici..." required></textarea>
      <button type="submit">Publier mon avis</button>
      <p class="note-info">Votre avis sera visible après validation par notre équipe.</p>
    </form>
  </div>

  <?php else: ?>

  <!-- Affiché SEULEMENT si non connecté -->
  <div class="avis-connexion-requise">
    <p>Vous devez être connecté pour laisser un avis.</p>
    <button type="button" id="btnAllerConnexion">Se connecter</button>
  </div>

  <?php endif; ?>
</section>

  <section id="contact">
    <div id="container">
      <h2>Contact Us</h2>
      <div class="contact-wrap">
        <div class="contact-info">
          <!-- 📱 WhatsApp -->
          <i class="fas fa-phone"></i>
          <a href="https://wa.me/21699449677" target="_blank" style="text-decoration: none; color: inherit;">
            +216 25913424/ +216 52626924
          </a><br>

          <!-- 📧 Email -->
          <i class="fas fa-envelope"></i>
          <a href="mailto:fekihnehia06@gmail.com" style="text-decoration: none; color: inherit;">
            beautybar@gmail.com
          </a><br>        
            <i class="fas fa-map-marker-alt"></i> 
          <a href="https://www.google.com/maps/place/BeautyBaramal/@35.6721926,10.8657786,746m/data=!3m2!1e3!4b1!4m6!3m5!1s0x1302112e0e8073cd:0x4cadd08f7d9c2394!8m2!3d35.6721926!4d10.8657786!16s%2Fg%2F11rd00_xp0!18m1!1e1?entry=ttu&g_ep=EgoyMDI2MDgxOS4wIKXMDSoASAFQAw%3D%3D" style="text-decoration: none; color: inherit;"> BeautyBarAmal Bouhjar, Monastir</a>
        </div>
      </div>
    </div>       
  </section>
  <footer>
    <div class="footer-logo">
      <h1>BeautyBarAmal</h1>
      <p>Révélez votre beauté, sublimez votre confiance.</p>
    </div>
    <div class="footer-nav">
      <h2>navigation</h2>
      <a href="#home">Acceuil</a><br>
      <a href="#about">à propos</a><br>
      <a href="#products">Products</a><br>
      <a href="#review">avis</a><br>
      <a href="#contact">Contacter</a><br>
    </div>
    <div class="footer-info">
      <i class="fas fa-map-marker-alt">   </i>BeautyBaramal Bouhjar, Monastir
      <i class="fas fa-phone"> </i> (+216)25913424/ (+216)52626924
      <i class="fas fa-envelope"> </i>beautybar@gmail.com             
    </div>
  </footer>
  <script>
  let lastScrollTop = 0;
  const navbar = document.querySelector(".navbar");

  window.addEventListener("scroll", function () {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    if (scrollTop === 0) {
      navbar.classList.remove("scrolled-up", "scrolled-down");
    } else if (scrollTop < lastScrollTop) {
      // Scrolling up
      navbar.classList.remove("scrolled-down");
      navbar.classList.add("scrolled-up");
    } else {
      // Scrolling down
      navbar.classList.remove("scrolled-up");
      navbar.classList.add("scrolled-down");
    }

    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
  });
</script>

</body>
</html>