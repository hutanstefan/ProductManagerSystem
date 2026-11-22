var deleteModal = document.getElementById('delete-modal');

if (deleteModal) {
    var deleteId = document.getElementById('delete-product-id');
    var deleteName = document.getElementById('delete-product-name');
    var cancelDelete = document.getElementById('cancel-delete');
    var deleteForm = deleteModal.querySelector('form');
    var confirmDelete = deleteModal.querySelector('.delete-confirm');
    var deleteButton = null;

    document.querySelectorAll('.delete-button').forEach(function (button) {
        button.addEventListener('click', function () {
            deleteButton = button;
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

    deleteModal.addEventListener('click', function (event) {
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDeleteModal();
        }
    });

    function showFlash(type, text) {
        var main = document.querySelector('main.container');
        var wrapper = document.createElement('div');
        var message = document.createElement('p');

        message.className = 'flash-message ' + type;
        message.textContent = text;
        message.setAttribute('role', 'alert');
        wrapper.appendChild(message);
        main.insertBefore(wrapper, main.children[1]);

        setTimeout(function () {
            wrapper.remove();
        }, 5000);
    }

    deleteForm.addEventListener('submit', function (event) {
        event.preventDefault();
        confirmDelete.disabled = true;

        fetch(deleteForm.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(deleteForm)
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Request failed');
            }
            return response.json();
        }).then(function (result) {
            if (result.success) {
                if (deleteButton) {
                    deleteButton.closest('tr').remove();
                }
                var tbody = document.querySelector('.products-table tbody');
                if (tbody && tbody.querySelectorAll('tr').length === 0) {
                    var row = document.createElement('tr');
                    var cell = document.createElement('td');
                    row.appendChild(cell);
                    cell.className = 'empty-state';
                    cell.colSpan = 7;
                    cell.textContent = 'No products found';
                    tbody.appendChild(row);
                }
                closeDeleteModal();
                showFlash('success', result.message);
            } else {
                closeDeleteModal();
                showFlash('error', result.message);
            }
        }).catch(function () {
            closeDeleteModal();
            showFlash('error', 'The product could not be deleted. Please try again');
        }).then(function () {
            confirmDelete.disabled = false;
        });
    });
}
