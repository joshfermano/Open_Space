document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('searchInput');
  const categoryFilters = document.querySelectorAll('.categoryFilter');
  const roomCardsContainer = document.getElementById('roomcards');

  // Debounce function to limit API calls
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

  // Function to fetch and display rooms
  async function fetchRooms() {
    const searchTerm = searchInput.value.trim();
    const selectedCategories = Array.from(categoryFilters)
      .filter((checkbox) => checkbox.checked)
      .map((checkbox) => checkbox.value)
      .filter((value) => value); // Remove empty values

    try {
      const params = new URLSearchParams();
      if (searchTerm) params.append('search', searchTerm);
      if (selectedCategories.length > 0)
        params.append('categories', selectedCategories.join(','));

      const response = await fetch(
        `/openspace/src/api/home/searchRooms.php?${params}`
      );
      const data = await response.json();

      if (data.success) {
        displayRooms(data.rooms);
      } else {
        console.error('Error:', data.message);
      }
    } catch (error) {
      console.error('Error:', error);
    }
  }

  // Function to display rooms
  function displayRooms(rooms) {
    if (rooms.length === 0) {
      roomCardsContainer.innerHTML = `
                <div class="col-span-3 text-center text-gray-500 py-8">
                    No rooms found matching your criteria
                </div>
            `;
      return;
    }

    roomCardsContainer.innerHTML = rooms
      .map(
        (room) => `
            <div class="shadow-lg w-[300px] h-[470px] rounded-xl bg-white p-4 flex flex-col space-y-4">
                <img class="w-[200px] h-[200px] self-center cursor-pointer hover:scale-110 hover:rounded-lg transition duration-300"
                    src="${room.image_path}"
                    alt="${room.title}">
                
                <h1 class="text-xl font-bold text-center">${room.title}</h1>
                
                <div class="flex flex-col space-y-2 px-2">
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

  // Add event listeners
  searchInput.addEventListener('input', debounce(fetchRooms, 300));
  categoryFilters.forEach((checkbox) => {
    checkbox.addEventListener('change', fetchRooms);
  });

  // Initial load
  fetchRooms();
});
