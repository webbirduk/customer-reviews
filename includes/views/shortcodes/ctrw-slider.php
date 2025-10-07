<?php
/**
* Customer Reviews Slider Template
*
* This template displays customer reviews in a responsive, stylish slider.
*
* @package ctrw
*/

if (!defined('ABSPATH')) {
   exit; // Exit if accessed directly.
}

// Fetch approved reviews from the database model.
$reviews_model = new CTRW_Review_Model();
$reviews = $reviews_model->get_reviews('approved');

// Get plugin settings to determine how many reviews to show.
$settings = get_option('customer_reviews_settings', []);
$reviews_per_page = $settings['reviews_per_page'] ?? 10;
$reviews = array_slice($reviews, 0, $reviews_per_page);
$review_count = count($reviews);
?>

<style>
 

   /* ========== Main Slider Container ========== */
   .ctrw-slider-container {
       max-width: 1200px;
       margin: 40px auto;
       padding: 0 40px; /* Increased padding to make space for external arrows */
       position: relative;
       font-family: 'Inter', sans-serif;
       color: #333;
   }

   /* ========== Slider Header ========== */
   .ctrw-slider-header {
       text-align: center;
       margin-bottom: 30px;
   }

   .ctrw-slider-header h2 {
       font-size: 28px;
       font-weight: 700;
       margin: 0 0 10px;
       color: #111;
   }
  
   .ctrw-slider-header .ctrw-header-rating {
       color: #FFC107;
       font-size: 24px;
       margin-bottom: 10px;
   }

   .ctrw-slider-header p {
       font-size: 16px;
       color: #666;
       margin: 0;
   }

   .ctrw-slider-header .google-logo {
       height: 24px;
       margin-top: 15px;
       opacity: 0.8;
   }

   /* ========== New Navigation Wrapper ========== */
   .ctrw-slider-navigation-wrapper {
       position: relative;
   }

   /* ========== Slider Wrapper ========== */
   .ctrw-slider-wrapper {
       overflow: hidden;
   }

   /* ========== Slides Track ========== */
   .ctrw-slider-slides {
       display: flex;
       transition: transform 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
       gap: 20px;
   }
  
   /* Centering logic for fewer reviews than the viewport can show */
   .ctrw-slider-slides.center-items {
       justify-content: center;
   }

   /* When centered, prevent individual slides from growing and set a max-width for consistency */
   .ctrw-slider-slides.center-items .ctrw-slider-slide {
       flex-grow: 0;
       max-width: 380px; /* Ensures consistent and pleasant card width */
   }


   /* ========== Individual Slide/Card ========== */
   .ctrw-slider-slide {
       min-width: calc((100% - 40px) / 3); /* (100% - total_gap) / num_slides */
       background: #fff;
       border-radius: 12px;
       border: 1px solid #e5e7eb;
       box-shadow: 0 4px 12px rgba(0,0,0,0.05);
       padding: 25px;
       box-sizing: border-box;
       display: flex;
       flex-direction: column;
   }

   /* ========== Card Content ========== */
.ctrw-slider-rating-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 3px;
}

   .ctrw-reviewer-avatar {
       width: 44px;
       height: 44px;
       border-radius: 50%;
       display: flex;
       align-items: center;
       justify-content: center;
       font-weight: 600;
       font-size: 16px;
       flex-shrink: 0;
       background-color: #0073aa;
       color: #fff;
   }

   .ctrw-slider-author-info {
       flex-grow: 1;
   }

  .ctrw-slider-author-name {
       font-weight: 600;
       font-size: 18px;
       color: #111;
    }
  
   .ctrw-slider-date {
       font-size: 14px;
       color: #777;
   }

   .ctrw-slider-rating-wrapper {
       display: flex;
       align-items: center;
       gap: 8px;
       margin-bottom: 15px;
   }

   .ctrw-slider-rating {
       color: #FFC107;
       font-size: 18px;
   }
  
   .ctrw-verified-icon {
       width: 18px;
       height: 18px;
       color: #0073aa;
   }

   .ctrw-slider-content {
       font-size: 15px;
       line-height: 1.6;
       color: #444;
       flex-grow: 1;
       overflow: hidden;
       max-height: 120px; /* Approx 5 lines */
       position: relative;
   }
  
   .ctrw-slider-content.expanded {
       max-height: none;
   }
  
   .ctrw-read-more {
       background: #e9e9e9;
       border: none;
       color: #0073aa;
       font-weight: 600;
       cursor: pointer;
       padding: 15px 15px 15px 15px;
       font-size: 14px;
       margin-top: 10px;
       border-radius: 4px
    }

   /* ========== Slider Controls ========== */
   .ctrw-slider-controls {
       display: flex;
       justify-content: center;
       align-items: center;
       gap: 15px;
       margin-top: 30px;
   }

   .ctrw-slider-prev,
   .ctrw-slider-next {
       position: static;
       transform: none;
       background: #fff;
       color: #333;
       border: 1px solid #e0e0e0;
       border-radius: 50%;
       width: 40px;
       height: 40px;
       cursor: pointer;
       display: flex;
       align-items: center;
       justify-content: center;
       box-shadow: 0 2px 8px rgba(0,0,0,0.1);
       transition: all 0.2s ease;
       flex-shrink: 0;
   }

   .ctrw-slider-prev:hover,
   .ctrw-slider-next:hover {
       background: #f5f5f5;
       transform: scale(1.05);
   }
  
   .ctrw-slider-controls-dots {
       display: flex;
       align-items: center;
       gap: 8px;
   }

   .ctrw-slider-dot {
       display: inline-block;
       width: 10px;
       height: 12px;
       background: #ccc;
       border-radius: 50%;
       border: none;
       margin: 0;
       cursor: pointer;
       transition: background 0.3s;
   }

   .ctrw-slider-dot.ctrw-slider-active {
       background: #0073aa;
   }

   /* ========== Responsive Adjustments ========== */
   @media (max-width: 992px) {
       .ctrw-slider-slide {
           min-width: calc((100% - 20px) / 2); /* 2 slides */
       }
   }

   @media (max-width: 768px) {
       .ctrw-slider-container {
           padding: 0 20px;
       }
       .ctrw-slider-slide {
           min-width: 100%; /* 1 slide */
       }
   }
</style>

<div class="ctrw-slider-container">


   <div class="ctrw-slider-navigation-wrapper">
       <div class="ctrw-slider-wrapper">
           <div class="ctrw-slider-slides <?php if ($review_count < 3) echo 'center-items'; ?>">
               <?php
               foreach ($reviews as $review) {
                   // Settings for each review
                   $show_city = !empty($settings['show_city']);
                   $date_format_setting = $settings['date_format'] ?? 'MM/DD/YYYY';
                   $formatted_date = '';
                   if (!empty($review->created_at)) {
                       $timestamp = strtotime($review->created_at);
                       $date_format = str_replace(['MM', 'DD', 'YYYY'], ['m', 'd', 'Y'], $date_format_setting);
                       $formatted_date = date($date_format, $timestamp);
                   }
               ?>
               <div class="ctrw-slider-slide">
                   <div class="ctrw-slider-meta">
                  
                       <div class="ctrw-slider-author-info">
                           <div class="ctrw-slider-author-name"><?= esc_html($review->name); ?></div>
                           <div class="ctrw-slider-date"><?= esc_html($formatted_date); ?></div>
                          

                       </div>
                   </div>
                  
                   <div class="ctrw-slider-rating-wrapper">
                       <div class="ctrw-slider-rating" aria-label="Rating: <?php echo (int) $review->rating; ?> out of 5 stars">
                           <?php for ($i = 0; $i < 5; $i++) : ?>
                               <span class="<?php echo $i < $review->rating ? 'filled' : 'empty'; ?>">&#9733;</span>
                           <?php endfor; ?>
                       </div>
                       <svg class="ctrw-verified-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1.41 14.59L6.17 13.17l1.41-1.41L10.59 14.17l5.83-5.83 1.41 1.41-7.24 7.24z"></path></svg>
                   </div>
                  
                   <div class="ctrw-slider-content">
                      <div class="ctrw-review-title"><?= esc_html($review->title); ?></div>
                       <p><?= esc_html($review->comment); ?></p>
                   </div>
               </div>
               <?php } ?>
           </div>
       </div>
   </div>

   <?php if ($review_count > 1): ?>
   <div class="ctrw-slider-controls">
       <button class="ctrw-slider-prev" aria-label="Previous review">&#10094;</button>
       <div class="ctrw-slider-controls-dots">
           <!-- Dots are dynamically generated by JavaScript -->
       </div>
       <button class="ctrw-slider-next" aria-label="Next review">&#10095;</button>
   </div>
   <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
   const container = document.querySelector('.ctrw-slider-container');
   if (!container) return;

   const slidesContainer = container.querySelector('.ctrw-slider-slides');
   const slides = container.querySelectorAll('.ctrw-slider-slide');
   const prevButton = container.querySelector('.ctrw-slider-prev');
   const nextButton = container.querySelector('.ctrw-slider-next');
   const dotsContainer = container.querySelector('.ctrw-slider-controls-dots');
   const controlsContainer = container.querySelector('.ctrw-slider-controls');
  
   if (slides.length === 0) return; // Exit if no reviews

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

       // Clamp currentIndex to be within valid bounds
       currentIndex = Math.max(0, Math.min(currentIndex, maxIndex));

       const slideWidth = slides.length > 0 ? slides[0].offsetWidth : 0;
       const gap = 20;
       const offset = currentIndex * (slideWidth + gap);
       slidesContainer.style.transform = `translateX(-${offset}px)`;

       // Dynamically create and update dots
       if (dotsContainer) {
           dotsContainer.innerHTML = ''; // Clear existing dots
           if (pages > 1) { // Only show dots if there's more than one page
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

       // Update button states
       if (prevButton) prevButton.disabled = currentIndex === 0;
       if (nextButton) nextButton.disabled = currentIndex === maxIndex;

       // Hide controls if all slides are visible
       const controlsVisible = slideCount > visibleSlides;
       if (controlsContainer) {
           controlsContainer.style.display = controlsVisible ? 'flex' : 'none';
       }
       slidesContainer.classList.toggle('center-items', !controlsVisible);
   }
  
   // "Read More" functionality
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

   // Event Listeners
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

   // Initial setup
   updateSlider();
});
</script>


