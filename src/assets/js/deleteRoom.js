function deleteRoom(roomId) {
  if (
    confirm(
      'Are you sure you want to delete this room? This action cannot be undone.'
    )
  ) {
    // Create and submit a form instead of using window.location
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/openspace/src/api/rooms/delete.php';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'room_id';
    input.value = roomId;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
  }
}

function deleteImage(imageId) {
  if (confirm('Delete this image?')) {
    fetch(`/openspace/src/api/rooms/deleteRoomImage.php?id=${imageId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          const imageContainer = document.querySelector(
            `[data-image-id="${imageId}"]`
          );
          if (imageContainer) {
            imageContainer.remove();
          }
        } else {
          alert(data.message || 'Failed to delete image');
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        alert('An error occurred while deleting the image');
      });
  }
}
