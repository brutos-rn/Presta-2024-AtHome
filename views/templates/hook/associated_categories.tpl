{if $associatedCategories|@count > 0}
    <div class="associated-categories">
        <h3>Catégories associées</h3>
        <div class="row">
            {foreach from=$associatedCategories item=category}
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="{$category.image_url}" class="card-img-top" alt="Image de la catégorie {$category.name}">
                        <div class="card-body">
                            <h5 class="card-title">{$category.name}</h5>
                            <a href="{$link->getCategoryLink($category.id_category)}" class="btn btn-primary">Voir la catégorie</a>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
{/if}