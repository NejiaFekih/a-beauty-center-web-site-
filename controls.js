function inscrie(){
    document.getElementById('signupForm').addEventListener('submit', async function(e) {
  e.preventDefault(); // ← empêche le rechargement/redirection de la page

  const nom = document.getElementById('np').value;
  const email = document.getElementById('email').value;
  const password = document.getElementById('mp').value;
  const messageEl = document.getElementById('messageInscription');

  try {
    const response = await fetch('inscription.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ nom, email, password })
    });

    const data = await response.json();

    if (data.success) {
      messageEl.style.color = 'green';
      messageEl.textContent = "Inscription réussie !";
      document.getElementById('signupForm').reset();
    } else {
      messageEl.style.color = 'red';
      messageEl.textContent = data.message || "Une erreur est survenue.";
    }

  } catch (error) {
    messageEl.style.color = 'red';
    messageEl.textContent = "Erreur de connexion au serveur.";
  }
});
}
function connecte(){

}