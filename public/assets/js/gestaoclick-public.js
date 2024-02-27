(function ($) {

    var item_id = 0;

    function add_fieldset(item_id) {
        var section = document.getElementById("gcw-section-quote");
        var fieldset = document.createElement("fieldset");
        fieldset.setAttribute("id", `gcw-quote-fieldset-${item_id}`);
        fieldset.setAttribute("class", "gcw-quote-fieldset");
        fieldset.innerHTML = `
            <legend>Item ${item_id}</legend>
            <div class="gcw-field-wrap">
                <label>Name</label>
                <input type="text" name="gcw-quote-name" />
            </div>
            <div class="gcw-field-wrap">
                <label>Description</label>
                <input type="text" name="gcw-quote-description" />
            </div>
            <div class="gcw-field-wrap">
            <label>Size</label>
                <select name="gcw-quote-size">
                    <option value="Selecionar" selected="selected">Selecionar</option>
                    <option value="PP">PP</option>
                    <option value="P">P</option>
                    <option value="M">M</option>
                    <option value="G">G</option>
                    <option value="GG">GG</option>
                    <option value="XG">XG</option>
                    <option value="XGG">XGG</option>
                    <option value="PS">Plus Size</option>
                </select>
            </div>
            <div class="gcw-field-wrap">
                <label>Quantity</label>
                <input type="number" name="gcw-quote-quantity" />
            </div>
            <a class="gcw-quote-remove" item_id="${item_id}">X</a>
        `;

        section.appendChild(fieldset);
    }

    $(window).on('load', () => {
        add_fieldset(++item_id);
    });

    $(document).on('click', '#gcw-quote-add-item', () => {
        var section = document.getElementById("gcw-section-quote");
        
        add_fieldset(section.children.length+1);
    });

    $(document).on('click', '.gcw-quote-remove', (element) => {
        var item_id = element.target.getAttribute('item_id');
        document.getElementById('gcw-quote-fieldset-' + item_id).remove();

        var section = document.getElementById("gcw-section-quote");
        var section_items = section.children;
        
        for (var i = 0; i < section_items.length; i++) {
            const fieldset = section_items[i];
            // console.log(fieldset);
            if(fieldset!=null) {
                fieldset.setAttribute('id', `gcw-quote-fieldset-${i+1}`);
                fieldset.firstElementChild.innerHTML = `Item ${i+1}`;
                fieldset.lastElementChild.setAttribute('item_id', i+1);
            }
        }
    });

})(jQuery);
