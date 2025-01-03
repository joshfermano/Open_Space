document.addEventListener('DOMContentLoaded', function () {
  const imageInput = document.getElementById('roomImages');
  const previewGrid = document.getElementById('imagePreviewGrid');
  const uploadLabel = previewGrid.querySelector('label');
  const MAX_IMAGES = 5;

  // Prevent form submission on enter key
  document.querySelector('form').addEventListener('keypress', function (e) {
    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
      e.preventDefault();
    }
  });

  imageInput.addEventListener('change', function () {
    // Check total number of files selected
    if (this.files.length > MAX_IMAGES) {
      alert(`You can only upload up to ${MAX_IMAGES} images`);
      this.value = '';
      return;
    }

    // Remove existing previews
    const existingPreviews = previewGrid.querySelectorAll('.image-preview');
    existingPreviews.forEach((preview) => preview.remove());

    // Add new previews
    Array.from(this.files).forEach((file, index) => {
      const reader = new FileReader();

      reader.onload = function (e) {
        const previewContainer = document.createElement('div');
        previewContainer.className = 'image-preview relative group';

        const img = document.createElement('img');
        img.src = e.target.result;
        img.className = 'w-full h-32 object-cover rounded-lg';

        // Create overlay div
        const overlay = document.createElement('div');
        overlay.className =
          'absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 ' +
          'flex items-center justify-center transition-opacity duration-200 rounded-lg';

        // Create delete button with icon
        const deleteBtn = document.createElement('button');
        deleteBtn.className =
          'text-white p-2 rounded-full hover:bg-red-600 transition-colors duration-200';
        deleteBtn.innerHTML = '<i class="fa-solid fa-trash"></i>';
        deleteBtn.type = 'button'; // Prevent form submission

        deleteBtn.onclick = function (evt) {
          evt.preventDefault();
          previewContainer.remove();

          // Create a new FileList without the deleted image
          const dt = new DataTransfer();
          const files = imageInput.files;

          for (let i = 0; i < files.length; i++) {
            if (i !== index) dt.items.add(files[i]);
          }

          imageInput.files = dt.files;

          // Show upload label if under max images
          if (
            previewGrid.querySelectorAll('.image-preview').length < MAX_IMAGES
          ) {
            uploadLabel.style.display = 'flex';
          }
        };

        overlay.appendChild(deleteBtn);
        previewContainer.appendChild(img);
        previewContainer.appendChild(overlay);
        previewGrid.insertBefore(previewContainer, uploadLabel);
      };

      reader.readAsDataURL(file);
    });

    // Hide upload label if max images reached
    if (this.files.length >= MAX_IMAGES) {
      uploadLabel.style.display = 'none';
    }
  });
});
