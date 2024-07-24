(function ($) {

    $(document).on('click', '#gcw-quote-add-item', () => {
        var section = document.getElementById("gcw-quote-section-items");
        add_fieldset(section.children.length + 1);
    });

    function add_fieldset(item_id) {
        var section = document.getElementById("gcw-quote-section-items");

        var fieldset = document.getElementById("gcw-quote-fieldset-1").cloneNode(true);
        fieldset.setAttribute("id", `gcw-quote-fieldset-${item_id}`);

        var legend = fieldset.getElementsByClassName("gcw-quote-fieldset-legend");
        legend[0].innerHTML = `Item ${item_id}`;

        var button_remove = fieldset.getElementsByClassName("gcw-quote-button-remove");
        button_remove[0].setAttribute('item_id', item_id);
        button_remove[0].setAttribute('style', 'visibility: visible');

        var name = fieldset.getElementsByClassName(`gcw-quote-name`);
        name[0].setAttribute('name', `gcw_item_nome-${item_id}`);
        name[0].value = '';

        var description = fieldset.getElementsByClassName(`gcw-quote-description`);
        description[0].setAttribute('name', `gcw_item_descricao-${item_id}`);
        description[0].value = '';

        var size = fieldset.getElementsByClassName(`gcw-quote-size`);
        size[0].setAttribute('name', `gcw_item_tamanho-${item_id}`);

        var quantity = fieldset.getElementsByClassName(`gcw-quote-quantity`);
        quantity[0].setAttribute('name', `gcw_item_quantidade-${item_id}`);
        quantity[0].value = '';

        section.appendChild(fieldset);
    }

    $(document).on('click', '.gcw-quote-button-remove', (event) => {
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
