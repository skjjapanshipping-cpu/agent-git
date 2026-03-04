<?php
/**
 * Template Name: SKJ Blog
 * Description: Blog listing page for SKJ Japan
 */
get_template_part('template-parts/skj-head');
get_template_part('template-parts/skj-header');
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
?>
<main>
<div class="header-spacer"></div>
<section class="page-hero">
    <div class="eyebrow">Blog</div>
    <h1>บทความ</h1>
    <p>เรื่องน่ารู้เกี่ยวกับการสั่งซื้อสินค้าและขนส่งจากญี่ปุ่น</p>
    <div class="breadcrumb"><a href="<?php echo home_url('/'); ?>">หน้าแรก</a><span>/</span><span style="color:rgba(255,255,255,0.8);">บทความ</span></div>
</section>

<section class="section">
    <div class="section-inner">
        <?php
        // Featured post (latest)
        $featured = new WP_Query(array('posts_per_page' => 1, 'post_status' => 'publish'));
        if ($featured->have_posts()) : $featured->the_post();
            $thumb = get_the_post_thumbnail_url(get_the_ID(), 'large');
            if (!$thumb) $thumb = 'https://skjjapanshipping.com/wp-content/uploads/2023/12/1-7.png';
        ?>
        <a href="<?php the_permalink(); ?>" class="featured-post animate-item" style="text-decoration:none;color:inherit;">
            <img class="featured-img" src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>">
            <div class="featured-content">
                <span class="featured-tag">บทความล่าสุด</span>
                <h2><?php the_title(); ?></h2>
                <p class="excerpt"><?php echo wp_trim_words(get_the_excerpt(), 30); ?></p>
                <span class="read-more" style="display:inline-flex;align-items:center;gap:6px;">อ่านต่อ <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>
        <?php endif; wp_reset_postdata(); ?>

        <!-- Blog Grid -->
        <div class="blog-grid">
            <?php
            $blog_offset = ($paged == 1) ? 1 : (($paged - 1) * 9) + 1;
            $blog_posts = new WP_Query(array(
                'posts_per_page' => 9,
                'post_status' => 'publish',
                'offset' => $blog_offset
            ));
            if ($blog_posts->have_posts()) : while ($blog_posts->have_posts()) : $blog_posts->the_post();
                $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                if (!$thumb) $thumb = 'https://skjjapanshipping.com/wp-content/uploads/2023/12/1-7.png';
                $cats = get_the_category();
                $cat_name = !empty($cats) ? $cats[0]->name : 'บทความ';
            ?>
            <a href="<?php the_permalink(); ?>" class="blog-card animate-item" style="text-decoration:none;color:inherit;">
                <img class="thumb" src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>">
                <div class="content">
                    <span class="tag"><?php echo esc_html($cat_name); ?></span>
                    <h3><?php the_title(); ?></h3>
                    <div class="date"><i class="far fa-calendar"></i> <?php echo get_the_date('j M Y'); ?></div>
                </div>
            </a>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <?php
        $total_posts = wp_count_posts()->publish;
        $total_pages = max(1, ceil(($total_posts - 1) / 9));
        if ($total_pages > 1) :
        ?>
        <div style="display:flex;justify-content:center;gap:8px;margin-top:40px;">
            <?php for ($i = 1; $i <= $total_pages; $i++) :
                $active = ($i == $paged) ? 'background:var(--red);color:white;' : 'background:var(--gray-100);color:var(--gray-700);';
            ?>
            <a href="<?php echo esc_url(add_query_arg('paged', $i)); ?>" style="<?php echo $active; ?>width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.88rem;"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; wp_reset_postdata(); else : ?>
        </div>
        <p style="text-align:center;color:var(--gray-400);padding:40px;">ยังไม่มีบทความ</p>
        <?php endif; ?>
    </div>
</section>

<?php get_template_part('template-parts/skj-footer'); ?>
<script>
window.addEventListener('scroll',function(){document.getElementById('header').classList.toggle('scrolled',window.scrollY>50);document.getElementById('scrollTop').classList.toggle('show',window.scrollY>400);},{passive:true});
var obs=new IntersectionObserver(function(e){e.forEach(function(e){if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target);}});},{threshold:0.05,rootMargin:'0px 0px 200px 0px'});
document.querySelectorAll('.animate-item').forEach(function(el,i){el.style.transitionDelay=(i%6)*0.08+'s';obs.observe(el);});
setTimeout(function(){document.querySelectorAll('.animate-item:not(.visible)').forEach(function(el){el.classList.add('visible');});},150);
</script>
<?php wp_footer(); ?>
</body></html>
