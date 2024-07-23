(function ($) {
    $(document).ready(function () {
        const add_to_cart_button = document.getElementsByClassName('single_add_to_cart_button')[0];
        const gcw_add_to_quote_button = document.getElementById("gcw_add_to_quote_button");

        // Copy CSS properties from WooCommerce Add to Cart original button
        const computedStyles = window.getComputedStyle(add_to_cart_button);
        const styles = [
            'background-color', 
            'color', 
            'padding', 
            'margin', 
            'border', 
            'border-radius', 
            'height', 
            'font-size', 
            'font-family',
            'vertical-align',
            'float', 
            'line-height',
        ];
        for(let style of styles){
            gcw_add_to_quote_button.style[style] = computedStyles.getPropertyValue(style);
        }

        add_to_cart_button.remove();
        // document.querySelector('input[name="quantity"]').setAttribute('min', '10'); // TODO: Define min value in plugin settings
    });
})(jQuery);