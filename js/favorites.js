/* ==========================================================================
   GYMZONE - Favorites Manager (favorites.html)
   ========================================================================== */

document.addEventListener("DOMContentLoaded", () => {
  renderFavoritesPage();
});

function renderFavoritesPage() {
  const grid = document.getElementById("favoritesGrid");
  if (!grid) return;

  const favoriteIds = getFavorites();
  const allGyms = getGymsFromStorage();
  const favGyms = allGyms.filter(g => favoriteIds.includes(g.id));

  if (favGyms.length === 0) {
    grid.innerHTML = `
      <div class="empty-state">
        <i class="far fa-heart empty-icon"></i>
        <h3 class="empty-title">No Favorite Gyms Saved</h3>
        <p class="empty-desc">Click the heart icon on any gym card while exploring to save gyms to your favorites list.</p>
        <a href="explore.html" class="btn btn-primary"><i class="fas fa-search"></i> Explore Gyms</a>
      </div>
    `;
    return;
  }

  grid.innerHTML = favGyms.map(gym => createGymCardHTML(gym)).join("");
  updateFavoriteIcons();
}
