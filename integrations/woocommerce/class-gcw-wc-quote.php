<?

class GCW_GC_Quote {

    // private $item = array(
    //     'product_id' => '',
    //     'quantity' => 0,
    // );

    private $date_created;
    private $id = 0;
    private $user_id;
    private $status = 'open' | 'closed';
    private $items = array();

    public function __construct($user_id = null)
    {
        $this->date_created = current_time('mysql');
        $this->id += 1;
        $this->user_id = $user_id;
        $this->status = 'open';
    }

    public function get_date_created()  { return $this->date_created; }

    public function get_id()            { return $this->id; }

    public function get_user_id()       { return $this->user_id; }

    public function get_items()         { return $this->items; }

    /**
     * Get the quantity of a given product in the quote list.
     * @param string $product_id id of a product to search in the quote list
     * @return array the quantity if $product_id is found, or null if not found
     */
    public function get_item_quantity(string $product_id)
    {
        foreach ($this->items as $item) {
            if ($item['product_id'] == $product_id) {
                return $item['quantity'];
            }
        }

        return false;
    }

    /**
     * Adds an item to the quote in the format of ['product_id' => 0, 'quantity' => 0].
     * @param 
     */
    public function add_item($new_item)
    {
        foreach ($this->items as $item) {
            if($new_item['product_id'] == $item['product_id']){
                $item['quantity'] += $new_item['quantity'];
                return;
            }
        }

        $this->items[] = $new_item;
    }

}