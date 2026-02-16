document.addEventListener("DOMContentLoaded", () => {
    // Gestion des parcours
    const addParcoursButton = document.querySelector(".add-parcours");
    const collectionHolder = document.querySelector(".parcours-collection");

    if (addParcoursButton && collectionHolder) {
        addParcoursButton.addEventListener("click", () => {
            addParcoursForm(collectionHolder);
        });

        // Add a remove button to existing items
        collectionHolder.querySelectorAll(".parcours-item").forEach((item) => {
            addRemoveButton(item);
        });
    }

    function addParcoursForm(collectionHolder) {
        const prototype = collectionHolder.dataset.prototype;
        const newForm = prototype.replace(
            /__name__/g,
            collectionHolder.children.length
        );
        const newFormDiv = document.createElement("div");
        newFormDiv.classList.add(
            "parcours-item",
            "mb-2",
            "d-flex",
            "align-items-center"
        );
        newFormDiv.innerHTML = newForm;

        // Ajouter la classe form-control à l'input
        const input = newFormDiv.querySelector("input");
        if (input) {
            input.classList.add("form-control", "me-2");
        }

        collectionHolder.appendChild(newFormDiv);
        addRemoveButton(newFormDiv);
    }

    function addRemoveButton(item) {
        let removeButton = item.querySelector(".remove-parcours");
        if (!removeButton) {
            removeButton = document.createElement("button");
            removeButton.type = "button";
            removeButton.classList.add(
                "btn",
                "btn-sm",
                "btn-outline-danger",
                "remove-parcours"
            );
            removeButton.textContent = "Supprimer";
            item.appendChild(removeButton);
        }
        removeButton.addEventListener("click", () => {
            item.remove();
        });
    }

    // Gestion des conditions de candidature
    const addConditionButton = document.querySelector(".add-condition");
    const conditionCollectionHolder = document.querySelector(
        ".con-candidature-collection"
    );

    if (addConditionButton && conditionCollectionHolder) {
        addConditionButton.addEventListener("click", () => {
            addConditionForm(conditionCollectionHolder);
        });

        // Add a remove button to existing condition items
        conditionCollectionHolder
            .querySelectorAll(".condition-item")
            .forEach((item) => {
                addRemoveConditionButton(item);
            });
    }

    function addConditionForm(collectionHolder) {
        const prototype = collectionHolder.dataset.prototype;
        const newForm = prototype.replace(
            /__name__/g,
            collectionHolder.children.length
        );
        const newFormDiv = document.createElement("div");
        newFormDiv.classList.add(
            "condition-item",
            "mb-2",
            "d-flex",
            "align-items-center"
        );
        newFormDiv.innerHTML = newForm;

        // Ajouter la classe form-control à l'input
        const input = newFormDiv.querySelector("input");
        if (input) {
            input.classList.add("form-control", "me-2");
        }

        collectionHolder.appendChild(newFormDiv);
        addRemoveConditionButton(newFormDiv);
    }

    function addRemoveConditionButton(item) {
        let removeButton = item.querySelector(".remove-condition");
        if (!removeButton) {
            removeButton = document.createElement("button");
            removeButton.type = "button";
            removeButton.classList.add(
                "btn",
                "btn-sm",
                "btn-outline-danger",
                "remove-condition"
            );
            removeButton.textContent = "Supprimer";
            item.appendChild(removeButton);
        }
        removeButton.addEventListener("click", () => {
            item.remove();
        });
    }

    // Gestion des documents de candidature
    const addDocumentButton = document.querySelector(".add-document");
    const documentCollectionHolder = document.querySelector(
        ".dos-candidature-collection"
    );
    console.log("Initialisation - Bouton document:", addDocumentButton);
    console.log(
        "Initialisation - Collection holder:",
        documentCollectionHolder
    );

    if (addDocumentButton && documentCollectionHolder) {
        console.log("Configuration des événements pour les documents");
        addDocumentButton.addEventListener("click", () => {
            console.log("Clic sur le bouton d'ajout de document");
            addDocumentForm(documentCollectionHolder);
        });

        // Add a remove button to existing document items
        const existingItems =
            documentCollectionHolder.querySelectorAll(".document-item");
        console.log("Documents existants trouvés:", existingItems.length);
        existingItems.forEach((item) => {
            addRemoveDocumentButton(item);
        });
    } else {
        console.log("Erreur: Éléments manquants", {
            bouton: addDocumentButton,
            collection: documentCollectionHolder,
        });
    }

    function addDocumentForm(collectionHolder) {
        console.log("Début de l'ajout d'un nouveau document");
        const prototype = collectionHolder.dataset.prototype;
        console.log("Prototype récupéré:", prototype ? "Oui" : "Non");
        const newForm = prototype.replace(
            /__name__/g,
            collectionHolder.children.length
        );
        const newFormDiv = document.createElement("div");
        newFormDiv.classList.add(
            "document-item",
            "mb-2",
            "d-flex",
            "align-items-center"
        );
        newFormDiv.innerHTML = newForm;

        // Ajouter la classe form-control à l'input
        const input = newFormDiv.querySelector("input");
        if (input) {
            input.classList.add("form-control", "me-2");
            console.log("Input configuré avec succès");
        }

        collectionHolder.appendChild(newFormDiv);
        addRemoveDocumentButton(newFormDiv);
        console.log("Nouveau document ajouté avec succès");
    }

    function addRemoveDocumentButton(item) {
        let removeButton = item.querySelector(".remove-document");
        if (!removeButton) {
            removeButton = document.createElement("button");
            removeButton.type = "button";
            removeButton.classList.add(
                "btn",
                "btn-sm",
                "btn-outline-danger",
                "remove-document"
            );
            removeButton.textContent = "Supprimer";
            item.appendChild(removeButton);
        }
        removeButton.addEventListener("click", () => {
            item.remove();
        });
    }
});
