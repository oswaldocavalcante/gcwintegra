(function ($) {

    function add_fieldset(item_id) {
        var section = document.getElementById("gcw-quote-section-items");
        var fieldset = document.createElement("fieldset");
        fieldset.setAttribute("id", `gcw-quote-fieldset-${item_id}`);
        fieldset.setAttribute("class", "gcw-quote-fieldset");
        fieldset.innerHTML = `
            <legend>Item ${item_id}</legend>
            <div class="gcw-field-wrap">
                <label>Nome</label>
                <input type="text" class="gcw-quote-name gcw-quote-input" name="gcw_item_nome-${item_id}"  required />
            </div>
            <div class="gcw-field-wrap">
                <label>Descrição</label>
                <input type="text" class="gcw-quote-description gcw-quote-input" name="gcw_item_descricao-${item_id}"  required />
            </div>
            <div class="gcw-field-wrap gcw-field-size">
            <label>Tamanho</label>
                <select class="gcw-quote-size gcw-quote-input" name="gcw_item_tamanho-${item_id}"  required >
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
            <div class="gcw-field-wrap gcw-field-quantity">
                <label>Quantidade</label>
                <input type="number" class="gcw-quote-quantity gcw-quote-input" name="gcw_item_quantidade-${item_id}" required value="10" min="10" inputmode="numeric" pattern="\d*" />
            </div>
            <a class="gcw-quote-remove" item_id="${item_id}">×</a>
        `;

        section.appendChild(fieldset);
    }

    $(window).on('load', () => {
        add_fieldset(1);
    });

    $(document).on('click', '#gcw-quote-add-item', () => {
        var section = document.getElementById("gcw-quote-section-items");
        add_fieldset(section.children.length+1);
    });

    $(document).on('click', '.gcw-quote-remove', (event) => {
        var item_id = event.target.getAttribute('item_id');
        document.getElementById('gcw-quote-fieldset-' + item_id).remove();

        var section = document.getElementById("gcw-quote-section-items");
        var section_items = section.children;
        
        for (var i = 0; i < section_items.length; i++) {
            const fieldset = section_items[i];
            if(fieldset!=null) {
                fieldset.setAttribute('id', `gcw-quote-fieldset-${i+1}`);
                fieldset.firstElementChild.innerHTML = `Item ${i+1}`;
                fieldset.lastElementChild.setAttribute('item_id', i+1);

                var name = fieldset.getElementsByClassName(`gcw-quote-name`);
                name[0].setAttribute('name', `gcw_item_nome-${i + 1}`);

                var description = fieldset.getElementsByClassName(`gcw-quote-description`);
                description[0].setAttribute('name', `gcw_item_descricao-${i + 1}`);

                var size = fieldset.getElementsByClassName(`gcw-quote-size`);
                size[0].setAttribute('name', `gcw_item_tamanho-${i + 1}`);

                var quantity = fieldset.getElementsByClassName(`gcw-quote-quantity`);
                quantity[0].setAttribute('name', `gcw_item_quantidade-${i + 1}`);
            }
        }
    });

    $(document).on('focusout', '#gcw-cliente-cpf-cnpj', (event) => {
        var cpf_cnpj = event.target.value;
        cpf_cnpj = cpf_cnpj.replace(/\D/g, "");

        if (cpf_cnpj.length < 14) { //CPF
            cpf_cnpj = cpf_cnpj.replace(/(\d{3})(\d)/, "$1.$2")
            cpf_cnpj = cpf_cnpj.replace(/(\d{3})(\d)/, "$1.$2")
            cpf_cnpj = cpf_cnpj.replace(/(\d{3})(\d{1,2})$/, "$1-$2")
        } else { //CNPJ
            cpf_cnpj = cpf_cnpj.replace(/^(\d{2})(\d)/, "$1.$2")
            cpf_cnpj = cpf_cnpj.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3")
            cpf_cnpj = cpf_cnpj.replace(/\.(\d{3})(\d)/, ".$1/$2")
            cpf_cnpj = cpf_cnpj.replace(/(\d{4})(\d)/, "$1-$2")
        }
        event.target.value = cpf_cnpj;
    });

})(jQuery);
