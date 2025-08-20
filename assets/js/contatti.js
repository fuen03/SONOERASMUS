document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("searchForm");
  const queryInput = document.getElementById("q");
  const groups = document.querySelectorAll(".card-grid");

  // Ricerca
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const term = queryInput.value.toLowerCase().trim();

    groups.forEach(group => {
      group.querySelectorAll(".card").forEach(card => {
        card.style.display = card.dataset.text.includes(term) ? "" : "none";
      });
    });
  });

  // Suggerisci
  document.getElementById("formSuggerisci").addEventListener("submit", (e) => {
    e.preventDefault();
    alert("Contatto suggerito (demo).");
    e.target.reset();
  });

  // Messaggio
  document.getElementById("formMessaggio").addEventListener("submit", (e) => {
    e.preventDefault();
    alert("Messaggio inviato! (demo).");
    e.target.reset();
  });
});