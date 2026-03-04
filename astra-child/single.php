<?php
/**
 * Single Post Template - SKJ Japan
 * Overrides Astra's single.php for blog posts
 */
get_template_part('template-parts/skj-head');
get_template_part('template-parts/skj-header');
?>
<main>
<div class="header-spacer"></div>

<?php if (have_posts()) : while (have_posts()) : the_post();
    $thumb = get_the_post_thumbnail_url(get_the_ID(), 'large');
    $cats = get_the_category();
    $cat_name = !empty($cats) ? $cats[0]->name : 'บทความ';
?>

<!-- PAGE HERO -->
<section class="page-hero">
    <div class="eyebrow">บทความ</div>
    <h1><?php the_title(); ?></h1>
    <div class="post-meta-hero">
        <span><i class="far fa-calendar"></i> <?php echo get_the_date('j M Y'); ?></span>
        <span><i class="far fa-folder"></i> <?php echo esc_html($cat_name); ?></span>
        <span><i class="far fa-clock"></i> <?php echo max(1, ceil(mb_strlen(strip_tags(get_the_content())) / 500)); ?> นาทีอ่าน</span>
    </div>
</section>

<!-- ARTICLE -->
<div class="article-wrap">
    <div class="article-card">
        <?php if ($thumb) : ?>
        <figure style="margin:0;border-radius:0;overflow:hidden;border:none;box-shadow:none;">
            <img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>" style="width:100%;height:360px;object-fit:cover;display:block;">
        </figure>
        <?php endif; ?>
        <div class="article-body">
            <?php the_content(); ?>

            <!-- SHARE -->
            <div class="share-bar">
                <span class="share-label"><i class="fas fa-share-nodes"></i> แชร์:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank" class="share-btn" title="Facebook" style="background:#fff;color:#1877F2;width:40px;height:40px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;border:1px solid #e2e8f0;">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://social-plugins.line.me/lineit/share?url=<?php echo urlencode(get_permalink()); ?>" target="_blank" class="share-btn" title="LINE" style="background:#fff;color:#06C755;width:40px;height:40px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;border:1px solid #e2e8f0;">
                    <i class="fab fa-line"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" target="_blank" class="share-btn" title="X" style="background:#fff;color:#000;width:40px;height:40px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;border:1px solid #e2e8f0;">
                    <i class="fab fa-x-twitter"></i>
                </a>
                <button class="share-btn copy" onclick="navigator.clipboard.writeText('<?php echo esc_url(get_permalink()); ?>');this.innerHTML='<i class=\'fas fa-check\'></i>';setTimeout(()=>{this.innerHTML='<i class=\'fas fa-link\'></i>';},2000);" title="Copy Link" style="background:#fff;color:var(--gray-500);width:40px;height:40px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;border:1px solid #e2e8f0;cursor:pointer;">
                    <i class="fas fa-link"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- RELATED POSTS -->
<?php
$related = new WP_Query(array(
    'posts_per_page' => 4,
    'post__not_in' => array(get_the_ID()),
    'post_status' => 'publish',
    'category__in' => wp_get_post_categories(get_the_ID()),
    'orderby' => 'rand'
));
if ($related->have_posts()) :
?>
<div class="related-section">
    <h2><i class="fas fa-newspaper" style="color:var(--red);margin-right:8px;"></i> บทความที่เกี่ยวข้อง</h2>
    <div class="related-grid">
        <?php while ($related->have_posts()) : $related->the_post();
            $rthumb = get_the_post_thumbnail_url(get_the_ID(), 'medium');
            if (!$rthumb) $rthumb = 'https://skjjapanshipping.com/wp-content/uploads/2023/12/1-7.png';
        ?>
        <a href="<?php the_permalink(); ?>" class="related-card animate-item" style="text-decoration:none;color:inherit;">
            <img src="<?php echo esc_url($rthumb); ?>" alt="<?php the_title_attribute(); ?>">
            <div class="rc-content">
                <h3><?php the_title(); ?></h3>
                <div class="rc-date"><i class="far fa-calendar" style="margin-right:4px;"></i> <?php echo get_the_date('j M Y'); ?></div>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; wp_reset_postdata(); ?>

<?php endwhile; endif; ?>

<?php get_template_part('template-parts/skj-footer'); ?>
<script>
window.addEventListener('scroll',function(){document.getElementById('header').classList.toggle('scrolled',window.scrollY>50);document.getElementById('scrollTop').classList.toggle('show',window.scrollY>400);},{passive:true});
var obs=new IntersectionObserver(function(e){e.forEach(function(e){if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target);}});},{threshold:0.05,rootMargin:'0px 0px 200px 0px'});
document.querySelectorAll('.animate-item').forEach(function(el,i){el.style.transitionDelay=(i%6)*0.08+'s';obs.observe(el);});
setTimeout(function(){document.querySelectorAll('.animate-item:not(.visible)').forEach(function(el){el.classList.add('visible');});},150);
</script>
<?php wp_footer(); ?>
</body></html>
