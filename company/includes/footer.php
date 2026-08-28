
<div id="footer">
<footer>
<p class="fr p5px">Copyright &copy; <?php echo date("Y"); ?> <?php echo get_page_settings(4);?>. All rights reserved.</p>
<p class="p5px"><a href="../terms.php" target="_blank">Terms of Use</a> | <a href="../privacy.php" target="_blank">Privacy Policy</a> | <a href="../contact_us.php" target="_blank">Link to Us</a></p>
</footer>
</div>

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.2.0/magnific-popup.min.css">

      <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.2.0/jquery.magnific-popup.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script>
    $(document).on('click', '.zoom_qj', function () {

        var imageUrl = $(this).attr('data-zoom');

        $.magnificPopup.open({
            items: {
                src: imageUrl
            },
            type: 'image'
        });

    });
    
    $(document).ready(function () {

        $('.view-more-btn').each(function () {

            var $button = $(this);

            var listSelector = $button.data('list');
            var visibleCount = parseInt($button.data('visible'), 10);

            var $items = $(listSelector).find('.view-more-item');

            if ($items.length <= visibleCount) {
                return;
            }

            $items.slice(visibleCount).addClass('is-hidden');

            $button.addClass('is-visible');

            $button.on('click', function () {

                var $hiddenItems = $items.filter('.is-hidden');

                if ($hiddenItems.length) {

                    $hiddenItems.removeClass('is-hidden');

                    $button.text('- Less');

                } else {

                    $items.slice(visibleCount).addClass('is-hidden');

                    $button.text('+ More');

                }

            });

        });

    });
</script>
<script>
    $(document).ready(function () {
        $('#products').slick({
            slidesToShow: 7,
            slidesToScroll: 1,
            arrows: true,
            dots: false,
            infinite: false,
            autoplay: false,
            autoplaySpeed: 3000,
            responsive: [
                {
                    breakpoint: 1200,
                    settings: {
                        slidesToShow: 5
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 3
                    }
                },
                {
                    breakpoint: 576,
                    settings: {
                        slidesToShow: 3
                    }
                },
                {
                    breakpoint: 426,
                    settings: {
                        slidesToShow: 3
                    }
                }
            ]
        });
    });
</script>