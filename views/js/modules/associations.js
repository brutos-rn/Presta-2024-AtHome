import { getTokenFromUrl } from './utils.js';

export function initAssociations(token) {
    displayAssociations(token);
}

export function displayAssociations(token) {
    fetch(`/admin-dev/modules/artcategorysearch/get-associations?_token=${token}`)
        .then(response => response.json())
        .then(data => {
            let associationContainer = document.querySelector('.association-container');
            if (!associationContainer) {
                associationContainer = document.createElement('div');
                associationContainer.classList.add('association-container');
                if (data.length !== 0) {
                    document.querySelector('.category-search-field').parentNode.appendChild(associationContainer);
                }
            }
            associationContainer.innerHTML = '';

            data.forEach(association => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin-dev/modules/artcategorysearch/delete-association?id_associated_category=${association.id_associated_category}&_token=${token}`;

                const item = document.createElement('div');
                item.classList.add('association-item');
                item.textContent = association.name;

                const deleteButton = document.createElement('button');
                deleteButton.classList.add('delete-button');
                deleteButton.type = 'submit';
                deleteButton.textContent = 'X';

                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    fetch(form.action, {
                        method: 'POST',
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            displayAssociations(token);
                        } else {
                            console.error(result.error);
                        }
                    });
                });

                item.appendChild(deleteButton);
                form.appendChild(item);
                associationContainer.appendChild(form);
            });
        });
}
