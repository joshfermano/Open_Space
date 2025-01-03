document.addEventListener('DOMContentLoaded', function () {
  let currentImageIndex = 0;
  const modal = document.getElementById('galleryModal');
  const modalImage = document.getElementById('modalImage');
  const imageCounter = document.getElementById('imageCounter');
  const totalImages = document.getElementById('totalImages');
  let isModalOpen = false;

  window.openGallery = function (index) {
    currentImageIndex = index;
    updateGalleryImage();
    modal.classList.remove('hidden');
    isModalOpen = true;
  };

  window.closeGallery = function () {
    modal.classList.add('hidden');
    isModalOpen = false;
  };

  window.prevImage = function () {
    if (!isModalOpen) return;
    currentImageIndex =
      (currentImageIndex - 1 + roomImages.length) % roomImages.length;
    updateGalleryImage();
  };

  window.nextImage = function () {
    if (!isModalOpen) return;
    currentImageIndex = (currentImageIndex + 1) % roomImages.length;
    updateGalleryImage();
  };

  function updateGalleryImage() {
    modalImage.src = roomImages[currentImageIndex];
    imageCounter.textContent = currentImageIndex + 1;
  }

  // Keyboard navigation
  document.addEventListener('keydown', function (e) {
    if (!isModalOpen) return;

    switch (e.key) {
      case 'Escape':
        closeGallery();
        break;
      case 'ArrowLeft':
        prevImage();
        break;
      case 'ArrowRight':
        nextImage();
        break;
    }
  });

  // Close modal when clicking overlay
  modal.addEventListener('click', function (e) {
    if (e.target === modal || e.target.classList.contains('modal-overlay')) {
      closeGallery();
    }
  });
});
