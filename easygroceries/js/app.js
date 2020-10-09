$(document).foundation()

$('.imageSlider').slick({
  dots: true,
  autoplay: true,
  arrows: true
});

var myCart = {};

function getDepartments() {
  var getDepartments = $.ajax({
    url: 'services/get_departments.php',
    type: 'POST',
    dataType: 'json'
  });

  getDepartments.done(function(data){
      var content= ``;
      $.each(data.departments, function(i, item) {
        var department_id = item.id;
        var department_name = item.name;
        if (i<10)
        content += `<li role="menuitem" class="left-li is-submenu-item is-dropdown-submenu-item"><a class="departmentList" onclick="loadPage(`+department_id+`)" href="#`+department_id+`">`+department_name+`</a></li>`;
        else
        content += `<li role="menuitem" class="right-li is-submenu-item is-dropdown-submenu-item"><a class="departmentList" onclick="loadPage(`+department_id+`)" href="#`+department_id+`">`+department_name+`</a></li>`;

      });

      $('#departmentMenu').html(content);

  });
}
function loadPage(id){
  var getDepartmentProducts = $.ajax({
    url: 'services/products_by_department.php',
    type: 'POST',
    data: {
      category_id: id
    },
    dataType: 'json'
  });

  getDepartmentProducts.done(function(data) {
    var content = ``;

    $.each(data.products, function(i, item) {
      var product_id = item.id;
      var name = item.product_name;
      var brand = item.brand;
      var image = item.image;
      var price = item.avg_price;

      content += `
                <div class="productList large-4 medium-6 small-12 cell">

                        <img src="` + image + `" alt="` + name + `">

                            <h4>` + name + `</h4>
                            <p>` + price + `</p>

                            <div class="add-to-cart">
                                <div class="addCart">
                                <div class="">
                                    <input type="button" id="plus-` + product_id + `" class="plus" data-product-id="` + product_id + `" value="+">
                                    &nbsp;<span id="quantity-` + product_id + `">1</span>&nbsp;
                                    <input type="button" id="minus-` + product_id + `" class="minus" data-product-id="` + product_id + `" value="-">
                                    <input type="button" class="add" data-id="cart" data-product-id="` + product_id + `" value="ADD">
                                </div>
                            </div>

                        </div>
                    </div>
                `;

    });
    $(".content-main").hide();
    $(".content-list").show();
    $(".products").html(content).show();
    $("#itemsTitle").text(data.category_name);
  });
};

$(document).ready(function() {

  getDepartments();



  $("#search").keyup(
    function() {
      var search = $(this).val();

      var getSearch = $.ajax({
        url: 'services/products_by_search.php',
        type: 'POST',
        data: {
          search: search
        },
        dataType: 'json'
      });

      getSearch.done(function(data) {
        var content = ``;

        $.each(data.products, function(i, item) {
          var product_id = item.id;
          var name = item.product_name;
          var brand = item.brand;
          var image = item.image;
          var price = item.avg_price;

          content += `
                    <div class="productList large-4 medium-6 small-12 cell">

                            <img src="` + image + `" alt="` + name + `">

                                <h4>` + name + `</h4>
                                <p>` + price + `</p>

                                <div class="add-to-cart">
                                    <div class="addCart">
                                    <div class="">
                                        <input type="button" id="plus-` + product_id + `" class="plus" data-product-id="` + product_id + `" value="+">
                                        &nbsp;<span id="quantity-` + product_id + `">1</span>&nbsp;
                                        <input type="button" id="minus-` + product_id + `" class="minus" data-product-id="` + product_id + `" value="-">
                                        <input type="button" class="add" data-id="cart" data-product-id="` + product_id + `" value="ADD">
                                    </div>
                                </div>

                            </div>
                        </div>
                    `;

        });
        $(".content-main").hide();
        $(".content-list").show();
        $(".products").html(content).show();
        $("#itemsTitle").text("Search Results");
      });

      getSearch.fail(function(jqXHR, textStatus) {
        alert('Something went wrong! (getSearch)' + textStatus);
      });

    }
  );

  Object.size = function(obj) {
    var size = 0,
      key;
    for (key in obj) {
      if (obj.hasOwnProperty(key)) size++;
    }
    return size;
  };

  $("#checkout").click(
    function() {
      var cart = "";
      var quantity = "";
      var customer_name = $("#customer_name").val();

      $.each(myCart, function(i, item) {
        if (cart == "") {
          cart = i;
          quantity = item;
        } else {
          // was cart += ", " + i;
          cart += "|" + i;
          // was quantity += ", " + item;
          quantity += "|" + item;
        }
      });


      var getProducts = $.ajax({
        url: 'services/save_cart.php',
        type: 'POST',
        data: {
          cart: cart,
          quantity: quantity,
          customer_name: customer_name
        },
        dataType: 'json'
      });

      getProducts.done(function(data) {

        var order_id = data.order_id;
        var name = data.name;
        alert("Thank you for your order " + name + ". Your ORDER NUMBER is " + order_id);

        myCart = {};
        $(".cart-wrapper").hide();
        $("customer_name").val("");
        $(".cartCircle").html("0");
      });


    }
  );

  $(".cartButton").click(
    function() {
      var cart = "";
      var quantity = "";
      $.each(myCart, function(i, item) {
        if (cart == "") {
          cart = i;
          quantity = item;
        } else {
          // was cart += ", " + i;
          cart += "|" + i;
          // was quantity += ", " + item;
          quantity += "|" + item;
        }
      });


      var getProducts = $.ajax({
        url: 'services/get_products.php',
        type: 'POST',
        data: {
          cart: cart,
          quantity: quantity
        },
        dataType: 'json'
      });

      getProducts.done(function(data) {

        var content = ``;

        $.each(data.products, function(i, item) {
          var it = item.item;
          var brand = item.brand;
          var name = item.name;
          var price = item.price;
          var quantity = item.quantity;
          var extended_price = item.extended_price;

          content += `
                     <div class="titles">
                            <div class="item">` + it + `</div>
                            <div class="product">` + name + `<br>
                            ` + brand + `
                            </div>
                            <div class="quantity">` + quantity + `</div>
                            <div class="price">$` + price + `</div>
                            <div class="extended-price">$` + extended_price + `</div>
                        </div>`;
        });

        $(".items").html(content);
        $(".subtotal-value").html(data.sub_total);
        $(".tax-value").html(data.hst);
        $(".total-value").html(data.total);

      });

      $(".cart-wrapper").show();
    }
  );

  $(".X").click(
    function() {
      $(".cart-wrapper").hide();
    }
  );

  $(document).on("click", "body .plus",
    function() {
      var product_id = $(this).attr("data-product-id");
      var quantity = $("#quantity-" + product_id).html();
      var quantity2 = parseInt(quantity) + 1;
      quantity2.toString();
      $("#quantity-" + product_id).html(quantity2);
    }
  );

  $(document).on("click", "body .minus",
    function() {
      var product_id = $(this).attr("data-product-id");
      var quantity = $("#quantity-" + product_id).html();
      var quantity2 = parseInt(quantity) - 1;
      if (quantity2 < 1) {
        quantity2 = 1;
      }
      quantity2.toString();
      $("#quantity-" + product_id).html(quantity2);
    }
  );

  $(document).on("click", "body .add",
    function() {

      var product_id = $(this).attr("data-product-id");


      var quantity = $("#quantity-" + product_id).html();

      var quantity2 = parseInt(quantity);

      if (myCart[product_id] != undefined) {
        var currentValue = myCart[product_id];
        myCart[product_id] = parseFloat(quantity) + parseFloat(currentValue);
      } else {
        myCart[product_id] = quantity;
      }

      var size = Object.size(myCart);
      console.log("size:: " + size);
      $(".cartCircle").html(size);

      $.each(myCart, function(i, item) {
        console.log("key:: " + i);
        console.log("value:: " + item);
      });
      $("#quantity-" + product_id).html("1");
    }
  );

  //alert("Dingo");

});
