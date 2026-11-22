var deleteModal = document.getElementById('delete-modal');

if (deleteModal) {
    var deleteId = document.getElementById('delete-product-id');
    var deleteName = document.getElementById('delete-product-name');
    var cancelDelete = document.getElementById('cancel-delete');

    document.querySelectorAll('.delete-button').forEach((button) => {
        button.addEventListener('click', () => {
            deleteId.value = button.dataset.id;
            deleteName.textContent = button.dataset.name;
            deleteModal.classList.add('is-visible');
            deleteModal.setAttribute('aria-hidden', 'false');
        });
    });

    function closeDeleteModal() {
        deleteModal.classList.remove('is-visible');
        deleteModal.setAttribute('aria-hidden', 'true');
    }

    cancelDelete.addEventListener('click', closeDeleteModal);

    deleteModal.addEventListener('click', (event) => {
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDeleteModal();
        }
    });
}
