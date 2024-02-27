<?php

class GCW_Orcamento_Item {
    private $id;
    private $item;

    public function __construct($id) {
        $this->change_item_id ($id);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_item() {
        return $this->item;
    }

    public function change_item_id($id) {
        $this->id = $id;
        $this->item = (`
            <fieldset class="gcw-quote-fieldset" id="gcw-quote-fieldset-${$this->id}">
                <legend>Item ${$this->id}</legend>
                <div class="gcw-field-wrap">
                    <label>Name</label>
                    <input type="text" name="gcw-quote-name-${$this->id}" />
                </div>
                <div class="gcw-field-wrap">
                    <label>Description</label>
                    <input type="text" name="gcw-quote-description-${$this->id}" />
                </div>
                <div class="gcw-field-wrap">
                <label>Size</label>
                    <select name="gcw-quote-size-${$this->id}">
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
                    <input type="number" name="gcw-quote-quantity-${$this->id}" />
                </div>
                <a id="gcw-quote-remove" item-id="${$this->id}">X</a>
            </fieldset>
        `);

        return $this->item;
    }
}