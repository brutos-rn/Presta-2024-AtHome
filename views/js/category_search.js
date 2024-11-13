document.addEventListener('DOMContentLoaded', function () {
    const searchField = document.querySelector('.category-search-field');

    function getTokenFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('_token');
    }

    const token = getTokenFromUrl();

    if (searchField) {
        searchField.addEventListener('input', function () {
            const query = searchField.value;

            if (query.length < 2) {
                var autocompleteResults = document.querySelector('.autocomplete-results');
                if (autocompleteResults) {
                    autocompleteResults.remove();
                }
                return
            }

            fetch(`/admin-dev/modules/artcategorysearch/search?query=${encodeURIComponent(query)}&_token=${token}`)
                .then(response => response.json())
                .then(data => {
                    
                    let autocompleteResults = document.querySelector('.autocomplete-results');
                    if (autocompleteResults) {
                        autocompleteResults.remove();
                    }

                    autocompleteResults = document.createElement('div');
                    autocompleteResults.classList.add('autocomplete-results');
                    searchField.insertAdjacentElement('afterend', autocompleteResults);

                    data.forEach(category => {
                        const item = document.createElement('div');
                        item.classList.add('autocomplete-item');
                        item.textContent = category.name;
                        item.dataset.categoryId = category.id_category;
                        autocompleteResults.appendChild(item);

                        item.addEventListener('click', function () {
                            const idAssociatedCategory = category.id_category;
                            fetch(`/admin-dev/modules/artcategorysearch/add-association?id_associated_category=${idAssociatedCategory}&_token=${token}`, {
                                method: 'POST'
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    displayAssociations();
                                } else {
                                    console.error(result.error);
                                }
                            });

                            searchField.value = category.name;
                            autocompleteResults.remove();
                        });
                    });
                });
        });
    }

    function displayAssociations() {
        fetch(`/admin-dev/modules/artcategorysearch/get-associations?_token=${token}`)
            .then(response => response.json())
            .then(data => {
                let associationContainer = document.querySelector('.association-container');
                if (!associationContainer) {
                    associationContainer = document.createElement('div');
                    associationContainer.classList.add('association-container');
                    if (data.length !== 0) {
                        searchField.parentNode.appendChild(associationContainer);
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
                                displayAssociations();
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
    
    displayAssociations();
});
