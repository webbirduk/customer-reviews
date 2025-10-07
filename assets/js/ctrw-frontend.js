jQuery(document).ready(function ($) {
    // Section: Review Submission Form
    const $reviewForm = $("#customer-reviews-form");
    if ($reviewForm.length) {
        $reviewForm.on("submit", function (e) {
            e.preventDefault();
            let formData = $reviewForm.serialize();
            formData += "&action=submit_review";

            $.ajax({
                url: ctrw_ajax.ajax_url,
                method: "POST",
                data: formData,
                success: function (data) {
                    let $message = $("#review-message");
                    if (data.success) {
                        $message.html("✅ Review submitted successfully!").css("color", "green");
                        $reviewForm[0].reset();
                    } else {
                        $message.html("❌ Error submitting review.").css("color", "red");
                    }
                }
            });
        });
    }

    // Section: Review List Pagination (AJAX)
    $(document).on('click', '.reviews-pagination a', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var target = $('#reviews-container');
        if (!target.length) return;

        var scrollTo = target.offset().top - 20;
        var page = $(this).data('page');
        var postId = target.data('post-id');
        
        target.addClass('loading');
        
        $.ajax({
            url: ctrw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'load_reviews_ajax',
                page: page,
                post_id: postId
            },
            success: function(response) {
                target.html(response);
                $('html, body').animate({scrollTop: scrollTo}, 300);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            },
            complete: function() {
                target.removeClass('loading');
            }
        });
    });

    // Section: Floating Widget
    const $floatingWidget = $('.ctrw-floating-widget');
    if ($floatingWidget.length) {
        $('.ctrw-floating-tab').on('click', function(e) {
            e.stopPropagation();
            $floatingWidget.toggleClass('active');
        });
        
        $('.ctrw-close-btn').on('click', function(e) {
            e.stopPropagation();
            $floatingWidget.removeClass('active');
        });
        
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.ctrw-floating-widget').length) {
                $floatingWidget.removeClass('active');
            }
        });
        
        $('.ctrw-view-all-btn').on('click', function() {
            // In a real scenario, this would likely link to a reviews page.
            // For now, we find the main reviews list on the page and scroll to it.
            const reviewsSection = $('#reviews-container');
            if (reviewsSection.length) {
                 $('html, body').animate({
                    scrollTop: reviewsSection.offset().top - 50
                }, 500);
            } else {
                alert('No main reviews list found on this page.');
            }
        });
    }

    // Section: Review Slider
    const sliderContainer = document.querySelector('.ctrw-slider-container');
    if (sliderContainer) {
       const slidesContainer = sliderContainer.querySelector('.ctrw-slider-slides');
       const slides = sliderContainer.querySelectorAll('.ctrw-slider-slide');
       const prevButton = sliderContainer.querySelector('.ctrw-slider-prev');
       const nextButton = sliderContainer.querySelector('.ctrw-slider-next');
       const dotsContainer = sliderContainer.querySelector('.ctrw-slider-controls-dots');
       const controlsContainer = sliderContainer.querySelector('.ctrw-slider-controls');
      
       if (slides.length === 0) return;

       let currentIndex = 0;
       const slideCount = slides.length;

       const getVisibleSlides = () => {
           if (window.innerWidth <= 768) return 1;
           if (window.innerWidth <= 992) return 2;
           return 3;
       };

       function updateSlider() {
           const visibleSlides = getVisibleSlides();
           const maxIndex = Math.max(0, slideCount - visibleSlides);
           const pages = maxIndex + 1;

           currentIndex = Math.max(0, Math.min(currentIndex, maxIndex));

           const slideWidth = slides.length > 0 ? slides[0].offsetWidth : 0;
           const gap = 20;
           const offset = currentIndex * (slideWidth + gap);
           slidesContainer.style.transform = `translateX(-${offset}px)`;

           if (dotsContainer) {
               dotsContainer.innerHTML = '';
               if (pages > 1) {
                   for (let i = 0; i < pages; i++) {
                       const dot = document.createElement('button');
                       dot.classList.add('ctrw-slider-dot');
                       if (i === currentIndex) {
                           dot.classList.add('ctrw-slider-active');
                       }
                       dot.dataset.index = i;
                       dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
                       dot.addEventListener('click', () => {
                           currentIndex = i;
                           updateSlider();
                       });
                       dotsContainer.appendChild(dot);
                   }
               }
           }

           if (prevButton) prevButton.disabled = currentIndex === 0;
           if (nextButton) nextButton.disabled = currentIndex === maxIndex;

           const controlsVisible = slideCount > visibleSlides;
           if (controlsContainer) {
               controlsContainer.style.display = controlsVisible ? 'flex' : 'none';
           }
           slidesContainer.classList.toggle('center-items', !controlsVisible);
       }
      
       slides.forEach(slide => {
           const content = slide.querySelector('.ctrw-slider-content');
           if (content.scrollHeight > content.clientHeight) {
               const readMoreBtn = document.createElement('button');
               readMoreBtn.textContent = 'Read more';
               readMoreBtn.className = 'ctrw-read-more';
               slide.appendChild(readMoreBtn);

               readMoreBtn.addEventListener('click', () => {
                   content.classList.toggle('expanded');
                   readMoreBtn.textContent = content.classList.contains('expanded') ? 'Read less' : 'Read more';
               });
           }
       });

       if (nextButton) {
           nextButton.addEventListener('click', () => {
               currentIndex++;
               updateSlider();
           });
       }

       if (prevButton) {
           prevButton.addEventListener('click', () => {
               currentIndex--;
               updateSlider();
           });
       }

       window.addEventListener('resize', updateSlider);
       updateSlider();
    }
});