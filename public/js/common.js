$(document).ready(function() {

    $("#category-view-list").click(function() {
        $(this).addClass('active');
        $("#category-view-grid").removeClass('active');
        $(".product-wrapper-grid").removeClass('col-lg-3 col-md-4 col-sm-6 product-wrapper-grid').addClass('product-wrapper-list');
        replaceUrl('list');
    });
    $("#category-view-grid").click(function() {
        $(this).addClass('active');
        $("#category-view-list").removeClass('active');
        $(".product-wrapper-list").removeClass('product-wrapper-list').addClass('col-lg-3 col-md-4 col-sm-6 product-wrapper-grid');
        replaceUrl('box');
    });

    $(function() {
        if(location.hash == "#list") {
            $( "#category-view-list" ).trigger( "click" );
        } else if (location.hash == "#box") {
            $( "#category-view-grid" ).trigger( "click" );
        }
    });

    function replaceUrl(viewType) {
        $("*[data-links='keep-view']").find('a').each(function() {
            var href = $(this).attr('href');
            href = href.replace(/#.+/g, '') + "#" + viewType;
            $(this).attr('href', href)
        });
        $("*[onclick*='document.location.href=']").each(function() {
            var href = $(this).attr('onclick');
            href = href.substring(0,href.length-1).replace(/#.+/g, '') + "#" + viewType + "'";
            $(this).attr('onclick', href)
        });
    }

    $('.gallery-view').bxSlider({
      pagerCustom: '#gallery-items',
      controls: false
    });


    var categories = $("#categories-groups");
    categories.owlCarousel({
        singleItem:true,
        pagination: false,
        autoPlay: 7000,
        paginationSpeed: 1500,
        slideSpeed: 1500,
        stopOnHover: true
    });
    $("#categories-groups-next").click(function(){
        categories.trigger('owl.next');
    });
    $("#categories-groups-prev").click(function(){
        categories.trigger('owl.prev');
    });


    var similar = $("#products-similar");
    similar.owlCarousel({
        items : 6,
        itemsDesktop : [1000,4],
        itemsDesktopSmall : [900,3],
        itemsTablet: [600,2],
        itemsMobile : false,
        pagination: false
    });
    $("#products-similar-next").click(function(){
        similar.trigger('owl.next');
    });
    $("#products-similar-prev").click(function(){
        similar.trigger('owl.prev');
    });

    var top = $("#products-top");
    top.owlCarousel({
        items : 6,
        itemsDesktop : [1000,4],
        itemsDesktopSmall : [900,3],
        itemsTablet: [600,2],
        itemsMobile : false,
        pagination: false
    });
    $("#products-top-next").click(function(){
        top.trigger('owl.next');
    });
    $("#products-top-prev").click(function(){
        top.trigger('owl.prev');
    });


    $( window ).scroll(function() {
        if($( window ).scrollTop() > 5)
        {
            $( ".navbar" ).addClass( "navbar-scrolled" );
        }
        else
        {
            $( ".navbar" ).removeClass( "navbar-scrolled" );
        }
    });

    $('[data-toggle="popover"]').popover();

    $('[data-toggle="tooltip"]').tooltip({
        html : true,
        title: function() {
            return $(this).children('.tooltip_title_wrapper').html();
        }
    });

    productModalEvent();

});

function productModalEvent(contextSelectorForEvent) {
    if (!contextSelectorForEvent) {
        contextSelectorForEvent = 'body';
    }
    $(contextSelectorForEvent).find('[data-product-modal]').click(function (event) {
        event.preventDefault();
        $('#productModal').modal('hide');
        $('#modal-preloader').show();
        $.post('/modal-' + $(this).attr('data-product-modal'), {})
            .done(function (data) {
                $('#modal-preloader').hide();
                $('body').append(data);
                $('#productModal').modal();
                $('#productModal').on('hidden.bs.modal', function () {
                    $(this).remove();
                });
            });

    });
}