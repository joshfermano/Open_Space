document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('searchInput');
  const categoryFilters = document.querySelectorAll('.categoryFilter');
  const roomcardsDiv = document.getElementById('roomcards');

  // Initial room load
  fetchRooms();

  // Search input event listener
  searchInput.addEventListener('input', debounce(fetchRooms, 300));

  // Category filter event listeners
  categoryFilters.forEach((filter) => {
    filter.addEventListener('change', fetchRooms);
  });

  function getSelectedCategories() {
    return Array.from(categoryFilters)
      .filter((checkbox) => checkbox.checked)
      .map((checkbox) => checkbox.value)
      .join(',');
  }

  async function fetchRooms() {
    const searchTerm = searchInput.value;
    const selectedCategories = getSelectedCategories();
    const params = new URLSearchParams();

    if (searchTerm) params.append('search', searchTerm);
    if (selectedCategories) params.append('categories', selectedCategories);

    try {
      const response = await fetch(
        `/openspace/src/api/home/searchRooms.php?${params}`
      );
      const data = await response.json();

      if (data.success) {
        displayRooms(data.rooms);
      } else {
        roomcardsDiv.innerHTML =
          '<p class="text-center text-gray-500">Error loading rooms</p>';
      }
    } catch (error) {
      console.error('Error:', error);
      roomcardsDiv.innerHTML =
        '<p class="text-center text-gray-500">Error loading rooms</p>';
    }
  }

  function displayRooms(rooms) {
    if (rooms.length === 0) {
      roomcardsDiv.innerHTML =
        '<p class="text-center text-gray-500">No rooms found</p>';
      return;
    }

    roomcardsDiv.innerHTML = rooms
      .map(
        (room) => `
                <div class="bg-white shadow-lg rounded-xl p-4 flex flex-col space-y-4">
                    <div class="aspect-w-16 aspect-h-9 relative overflow-hidden rounded-lg">
                        <img src="${room.image_path}"
                            alt="${room.title}"
                            class="w-full h-full object-cover hover:scale-105 transition duration-300" 
                            onerror="this.src='/openspace/src/assets/img/logo_black.jpg'"
                        />
                    </div>
                    <div class="flex flex-col flex-grow space-y-2">
                        <h3 class="text-xl font-bold text-center">${
                          room.title
                        }</h3>
                        <div class="space-y-2 px-2">
                            <p><span class="font-bold">Location:</span> ${
                              room.location
                            }</p>
                            <p><span class="font-bold">Price:</span> ₱${parseFloat(
                              room.price
                            ).toFixed(2)}/hr</p>
                            <p><span class="font-bold">Category:</span> ${
                              room.category_name
                            }</p>
                        </div>
                    </div>
                    <a href="/openspace/src/pages/rooms/viewRoom.php?id=${
                      room.room_id
                    }">
                        <button class="bg-black text-white w-full p-2 rounded-full border hover:border-black hover:bg-white hover:text-black hover:scale-95 transition-all duration-300">
                            View Room
                        </button>
                    </a>
                </div>
            `
      )
      .join('');
  }

  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }
});
