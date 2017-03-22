<?php



    $getID = $_REQUEST['product_id'];



    $product_id = null;

    //SELECT product info from database

    if ($select = $db -> prepare("SELECT p.*, c.category_name, c.category_webname FROM products AS p 
                INNER JOIN categories AS c ON p.category_id=c.category_id 
                WHERE p.product_id=?")) {
        $select->bind_param('s', $getID);
        $select->execute();
        $select->bind_result($product_id, $product_name, $product_webname, $category_id, $product_description, $product_photo, $product_type, $product_sku, $product_price, $category_name, $category_webname, $product_weight);
        $select->fetch();
        $select->close();
    }




    if (!$product_id) {

        header('Location: index.php');

        exit;

    }





    $group = array();
//$shopping_cart = [
//    1 => [
//        'type' => 'multi', //multi or single,
//        'items' => [
//            [
//                'diameter' => 1,
//                'hardware' => 2,
//                'type_id' => 22,
//                'qty' => 2
//            ],
//            [
//                'diameter' => 3,
//                'hardware' => 5,
//                'type_id' => 11,
//                'qty' => 23
//            ],
//        ]
//    ],
//    2 => [
//        'type' => 'single',
//        'qty' => 1
//    ],
//    3 => [
//        'type' => 'single',
//        'qty' => 2,
//        'hardware' => 11 //optional
//    ]
//];
//foreach($shopping_cart as $product_id => $product) {
//    if($product['type'] == 'multi') {
//            foreach($product['items'] as $item) {
//                $diameter = $item['diameter'];
//            }
//    }
//    else {
//        $qty = $product['qty'];
//    }
//}


if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    if(!isset($_COOKIE['cart']) || !is_array( $cart = json_decode($_COOKIE['cart'], true) )) {

        $cart = array();

    }

    $product_id = $_POST['product_id'];
    $write_cookie = false;
    if(!isset($cart[$product_id])) {
        $cart[$product_id] = [];
    }

    if(!$product_id || !is_numeric($product_id) || $product_id < 0) {
        $_SESSION['msg'] = 'Please select a valid product.';
    }
    else {
        $qty = $_POST['qty'];
        //multi item
        if(is_array($qty)) {
            $items = [];
            foreach($qty as $index => $q) {
                if(is_numeric($q) && $q > 0) {
                    if(isset($_POST['strut_diameter'][$index]) && isset($_POST['hardware'][$index]) && isset($_POST['type_id'][$index])) {
                        $items[] = [
                            'diameter' => (int) $_POST['strut_diameter'][$index],
                            'hardware' => (int) $_POST['hardware'][$index],
                            'type_id' => (int) $_POST['type_id'][$index],
                            'qty' => $q
                        ];
                    }
                }
            }
            //ensure we have at least one item in the cart
            if(count($items) > 0) {
                $cart[$product_id]['type'] = 'multi';
                //if there are previous items
                if(isset($cart[$product_id]['items']) && is_array($cart[$product_id]['items'])) {
                    $cart[$product_id]['items'] = array_merge($cart[$product_id]['items'], $items);
                }
                else {

                    $cart[$product_id]['items'] = $items;
                }
                $write_cookie = true;
            }
            else {
                $_SESSION['msg'] = 'Please make a selection.';
            }

        }
        elseif(is_numeric($qty) && $qty > 0) { //single
            $cart[$product_id] = ['type' => 'single', 'qty' => $qty];
            if(isset($_POST['hardware'])) {
                $cart[$product_id]['hardware'] = (int) $_POST['hardware'];
            }
            $write_cookie = true;
        }
        else {
            $_SESSION['msg'] = 'Please select a valid product.';
        }
    }

    if($write_cookie) {
        setcookie('cart', json_encode($cart), time() + (3 * 24 * 60 * 60), '/');
        header('Location: ./?page=cart&mode=add-to-cart' );
        exit;
    }

}

    //Get Dimensions Photo
    $file_uploadtype = "D";
    if ($selectfile = $db -> prepare("SELECT file_file FROM files WHERE product_id=? AND file_uploadtype=?")) {

    $selectfile->bind_param('ss', $getID, $file_uploadtype);

    $selectfile->execute();

    $selectfile->bind_result($file_file);

    $selectfile->fetch();

    $selectfile->close();

    }



    // Set select box options for 1-99

    $qty_dropdown = '';

    for($x = 1; $x <= 99; $x++) {

        $qty_dropdown .= '<option value="'.$x.'">'.$x.'</option>';

    }



    $page_title     = 'Paratech Strut Mounts';

    $page_class     = 'strut_mounts view';

    $page_keywords  = '';

    $page_desc      = '';



    include_once ('./assets/template/website/main.php');

?>



<!-- Begin the page content. -->

<div class="content_block">

    <div class="container">

        <div class="row">

            <div class="col-md-5  text-center product-photo">

                <!-- The Gallery as lightbox dialog, should be a child element of the document body -->
                <div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls">
                    <div class="slides"></div>
                    <h3 class="title"></h3>
                    <a class="prev">‹</a>
                    <a class="next">›</a>
                    <a class="close">×</a>
                    <a class="play-pause"></a>
                    <ol class="indicator"></ol>
                </div>

                <div id="links">
                <div class="mainimage">
                <a href="./assets/uploads/product_photos/<?php echo $product_photo; ?>">
                    <img src="./assets/uploads/product_photos/<?php echo $product_photo; ?>" alt="<?php echo $product_name; ?>" class="product_photo" />
                </a>
                </div>
                    <div class="clear"></div>
                 <?php
                //Get Dimensions Photo
                    $puploadtype = "P";
                    if ($selectimages = $db -> prepare("SELECT file_name, file_file FROM files WHERE product_id=? AND file_uploadtype=?")) {

                    $selectimages->bind_param('ss', $getID, $puploadtype);

                    $selectimages->execute();

                    $selectimages->bind_result($file_name, $thumbs);

                    while($selectimages->fetch()){

                        echo '<div class="thumbs"><a href="./assets/uploads/product_photos/'.$thumbs.'" title="'.$file_name.'"><img src="./assets/uploads/product_photos/'.$thumbs.'" width=100px alt="'.$file_name.'"></a></div>';
                    }

                    $selectimages->close();

                    }
                ?>
                </div>
                <div class="clear"></div>
                <script type="text/javascript">
                document.getElementById('links').onclick = function (event) {
                    event = event || window.event;
                    var target = event.target || event.srcElement,
                        link = target.src ? target.parentNode : target,
                        options = {index: link, event: event},
                        links = this.getElementsByTagName('a');
                    blueimp.Gallery(links, options);
                };
                </script>


                <br/>

                <br/>

                <h4>Product Dimensions</h4>

                <p><a href="./assets/uploads/product_photos/<?php echo $file_file; ?>" data-lightbox="image-1"><img src="./assets/uploads/product_photos/<?php echo $file_file; ?>" width="295px" /></a></p>

            </div>


            <div class="col-md-7">

                <div class="col-md-12 product_details">



                    <h1><?php echo $product_name; ?></h1>



                    <div class="required-product-addon product-addon product-addon-part">

                        <form method="post" id="add-to-cart-form" class="form" action="">
                            <input type="hidden" name="product_id" id="product_id" value="<?php echo $getID ?>">
                            <?php

                                if ($product_type =="strut"){

                                $parent_id = 1;

                                if ($selectstrutoptions = $db -> prepare("SELECT type_id, type_name, type_webname FROM variation_types WHERE type_parent=? ORDER BY type_id ASC")){
                                    $selectstrutoptions -> bind_param('s', $parent_id);
                                    $selectstrutoptions -> execute();
                                    $selectstrutoptions -> bind_result($type_id, $type_name, $type_webname);
                                    $selectstrutoptions -> store_result();
                                    if ($selectstrutoptions -> num_rows == 0) {

                                    }
                                    else
                                        {
                                            $grpnum = 0;
                                            while($selectstrutoptions -> fetch())
                                            {
												$grpnum++;
                                                $fgid = "fg_$grpnum";
                                                
                                                echo '<input type="hidden" id="type_name" value="strut">';
                                                echo '<input type="hidden" name="type_id[]" value="',$type_id,'">';
                                                echo '<div class="form-group" id="'.$fgid.'">';
                                                echo '<div class="partimage"><img src="./assets/img/'.$type_webname.'-'.$product_webname.'.jpg" /></div>';
                                                echo '<div class="partname">'.$type_name.'</div><br/>';
                                                echo '<div class="clear"></div>';
                                                echo '<div class="partdropdown">
                                                <select name="strut_diameter[]" id="strut_diameter_'.$grpnum.'" 
                                                class="strut-diameter form-control calculate"
                                                data-hardware-field="hardware_'.$grpnum.'" 
                                                data-qty-field="qty_'.$grpnum.'" data-group-id="'.$grpnum.'">
                                                <option value="0"><span style="font-color:red">*</span> Select Strut Diameter</option>';
                                                
                                                
                            // Prepare and execute the SELECT statement for strut diameter.
                            if ($selectdiameter = $db -> prepare("SELECT vd.data_id, vd.data_name, vd.data_webname, vd.data_price FROM variation_data AS vd INNER JOIN variation_pivot AS vp ON vd.data_id=vp.data_id WHERE vp.type_id=? ORDER BY vd.data_id ASC")) {
                                $selectdiameter -> bind_param('s', $type_id);
                                $selectdiameter  ->  execute();
                                $selectdiameter  ->  bind_result($data_id, $data_name, $data_webname, $data_price);
                                $selectdiameter -> store_result();
                                while($selectdiameter -> fetch()) {
                                    echo '<option value="'.$data_id.'" data-price="'.$data_price.'">'.ucwords($data_name).'</option>';
                                }
                                $selectdiameter -> close();
                            }
                                                echo '</select></div>';
                                                echo '<div class="partdropdown">
                                                <select name="hardware[]" disabled data-group-id="'.$grpnum.'" id="hardware_'.$grpnum.'" class="form-control calculate hardware"><option value="0"><span style="font-color:red">*</span> Select Hardware</option>';
                                                
                            $hardwarename ='hardware';
                            // Prepare and execute the SELECT statement for strut hardware.
                            if ($selecthardware = $db -> prepare("SELECT vd.data_id, vd.data_name, vd.data_webname, vd.data_price FROM variation_data AS vd INNER JOIN variation_pivot AS vp ON vd.data_id=vp.data_id INNER JOIN variation_types AS vt ON vp.type_id=vt.type_id WHERE vt.type_parent=? AND vt.type_webname=? ORDER BY vd.data_id ASC")) {
                                $selecthardware -> bind_param('ss', $type_id, $hardwarename);
                                $selecthardware  ->  execute();
                                $selecthardware  ->  bind_result($data_id, $data_name, $data_webname, $data_price2);
                                $selecthardware -> store_result();
                                while($selecthardware -> fetch()) {
                                    echo '<option value="'.$data_id.'" data-price="'.$data_price2.'">'.ucwords($data_name).' (+$'.number_format($data_price2,2).')</option>';
                                }
                                $selecthardware -> close();
                            }
                                                echo '</select></div>';
                                                echo '<div class="clear"></div>';
                                                echo '<div class="qtydropdown">
                                                <select name="qty[]" data-group-id="'.$grpnum.'" disabled id="qty_'.$grpnum.'" class="form-control part-qty qty"><option value="0">0 qty</option>'.$qty_dropdown.'</select></div>';
                                                echo '<div class="partprice" id="price">';
                                                echo '<span class="productprice" id="productprice_'.$grpnum.'">$'.$data_price;
                                                echo '</span>';
                                                echo '</div>';
                                                echo '<div class="clear"></div>';
                                                echo '</div>';
                                            }
                                            }
                                            $selectstrutoptions -> close();
                                    }
                            }
                            ?>


                            <!-- Product Price -->
                            <?php if ($product_type == "other"){ 
                            $grpnum = 0;?>

                                <input type="hidden" name="other" value="other">

                                <?php if ($product_saleprice > 0){
                                                 
                                //saleprice

    
                                }else{
  
                                echo '<input type="hidden" name="other_price" id="other_price_'.$grpnum.'" value="'.$product_price.'" data-price="'.$product_price.'" />';
 
                                echo '<div class="partprice" id="price">';
                                echo '<span class="productprice" id="productprice_'.$grpnum.'">$'.number_format($product_price,2);
                                echo '</span>';
                                echo '</div>';

                                echo '<div class="qtydropdown">
                                      <select name="qty" id="qty_'.$grpnum.'" disabled data-group-id="'.$grpnum.'" class="other-qty form-control part-qty"><option value="0">0 qty</option>'.$qty_dropdown.'</select></div>';

                                }
                                                               }?>
                            <div class="clear"></div>
                           
                            <!-- Non Strut Product Options -->
                            <?php if ($product_type == "other"){
    
                            

                                    $hardwarename ='hardware';
                                    // Prepare and execute the SELECT statement for strut hardware.
                                    if ($selecthardware = $db -> prepare("SELECT vd.data_id, vd.data_name, vd.data_webname, vd.data_price FROM variation_data AS vd INNER JOIN variation_pivot AS vp ON vd.data_id=vp.data_id INNER JOIN variation_types AS vt ON vp.type_id=vt.type_id WHERE vp.product_id=? AND vt.type_webname=? ORDER BY vd.data_id ASC")) {
                                        $selecthardware -> bind_param('ss', $getID, $hardwarename);
                                        $selecthardware  ->  execute();
                                        $selecthardware  ->  bind_result($data_id, $data_name, $data_webname, $data_price2);
                                        $selecthardware -> store_result();
                                        if ($selecthardware -> num_rows == 0) {

                                        }
                                        else {
                                            echo '<div class="partdropdown">
                                                <select name="hardware" id="hardware_'.$grpnum.'" data-group-id="'.$grpnum.'" class="other-hardware form-control calculate"><option value="0"><span style="font-color:red">*</span> Select Hardware</option>';
                                            while ($selecthardware->fetch()) {
                                                echo '<option value="'.$data_id.'" data-price="'.($data_price2 + $product_price).'">'.ucwords($data_name).' (+$'.number_format($data_price2,2).')</option>';
                                            }
                                            echo '</select>';
                                            echo '</div>';
                                            
                                            echo '<div class="partdropdown">&nbsp;</div>';
                                        }
                                        $selecthardware -> close();
                                    }

                            } ?>


                            <div class="col-md-9">
                                <br/>
                            <button type="submit" class='btn btn-primary w-100-pct'>Add To Cart</button>
                            </div>
                        </form>
                        <br/>
                        <div class="col-md-12">
                            <p>Category: <a href="<?php echo $domain; ?>/?page=<?php echo $category_webname; ?>"><?php echo $category_webname; ?></a></p>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
<script type="text/javascript">  
    jQuery(document).ready(function($){

       $('body').on('click', '.strut-diameter', function(){
          var hardware =  document.getElementById( $(this).attr('data-hardware-field') );
          var qty =  document.getElementById( $(this).attr('data-qty-field') );

           if( $(this).val() == '0' ) {
               hardware.selectedIndex = 0;
               qty.selectedIndex = 0;
               hardware.setAttribute('disabled', true);
               qty.setAttribute('disabled', true);

               noOptionSelected($(this).data('group-id'));
           }
           else {
               hardware.removeAttribute('disabled');
               qty.removeAttribute('disabled');
               if(hardware.selectedIndex == 0) {
                   hardware.selectedIndex = 1;
               }
               if(qty.selectedIndex == 0) {
                   qty.selectedIndex = 1;
               }
               fillprice($(this).data('group-id'));
           }
       });
        $('.hardware, .qty').on('change', function(){
            fillprice($(this).data('group-id'));
        });
        $(document).on('click', '.other-hardware', function(){
            var hardware =  $(this)[0];
            var qty =  $('.other-qty')[0];

            if( $(this).val() == '0' ) {
                qty.selectedIndex = 0;
                qty.setAttribute('disabled', true);

                noOptionSelected($(this).data('group-id'));
            }
            else {

                qty.removeAttribute('disabled');
                if(qty.selectedIndex == 0) {
                    qty.selectedIndex = 1;
                }
                console.log('qty dropdown: '+ qty.selectedIndex)
                calculateOtherPrice($(this).data('group-id'));
            }

        });
        $('.other-qty').on('change', function(){
            calculateOtherPrice($(this).data('group-id'));
        });
    });
function calculate_price(groupid){
    var total = 0;
	var selector = '#fg_' + groupid + ' .calculate'; 
	
    $(selector).each(function() {
           var price = Number($(this).find('option:selected').data('price'));
        if(price){
            //console.log('price',price);
            total += price;
        }
            //console.log($(this).data('price'));
    });
    var qty = $('#qty_' + groupid).val();
    //console.log('total',total,'qty',qty);
    return (total*qty).toFixed(2);
}

$('#strut_diameter, #qty, #hardware').on('change',function(){
    $('#productprice').html('$' + calculate_price());
});

function fillprice(groupid){
	$('#productprice_' + groupid).html('$' + calculate_price(groupid));
}
function calculateOtherPrice(groupid) {
    var price = parseInt($('#other_price_'+groupid).val());
    var qty = parseInt($('#qty_' + groupid).val());
    var hardware = $('#hardware_' + groupid+'>option:selected');

    console.log('price: ' + price);
    console.log('qty: ' + qty);
    console.log('hd: ' + hardware.attr('data-price'));
    //var hardware = document.getElementById('#hardware' + groupid);


    var total = (parseFloat(hardware.attr('data-price'))  * qty).toFixed(2);

    $('#productprice_' + groupid).html('$' + total);
}
function noOptionSelected(groupid) {
    $('#productprice_' + groupid).html('Select an option');

}
</script>