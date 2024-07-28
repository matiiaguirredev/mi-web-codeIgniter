        <!-- Start Blog Area -->
        <section id="blog" class="blog-area ptb-80">
            <div class="container">
                <div class="section-title">
                    <h2>News</h2>
                    <h3>Blog.</h3>
                    <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium,eaque ipsa quae ab illo inventore veritatis.</p>
                </div>
                <div class="row">
                    <?php /* debug($blog) ; */ ?>
                    <?php foreach ($blog as $key => $value) { ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="single-blog-post">
                                <div class="thumb thumb-img position-relative">
                                    <style>
                                        .thumb-img-<?= $key; ?> {
                                            background-image: url(<?= $value->img; ?>);
                                        }
                                    </style>
                                    <div class="thumb-img-<?= $key; ?> thumb-img-repite"></div>
                                    <img class="opacity-0" src="./api/bgimg/350/307" alt="portfolio">
                                    <!-- <img src="web/img/portfolio-2.jpg" alt="portfolio"> -->
                                    <div class="date">
                                        <span><?= dia_mes($value->create_at);?></span>
                                    </div>

                                    <div class="tag">
                                        <a href="#"><?= $value->categoria; ?></a>
                                    </div>
                                </div>

                                <div class="content">
                                    <h4><a href="single-blog.html"><?= $value->titulo; ?></a></h4>
                                    <p><?= $value->descrip_corta; ?></p>
                                </div>
                                <a href="single-blog/<?= $value->id; ?>" class="btn btn-primary">Read More <i class="fa fa-angle-double-right"></i></a>
                            </div>
                        </div>
                    <?php } ?>

                    <!-- <div class="col-lg-4 col-md-6">
                        <div class="single-blog-post">
                            <div class="thumb">
                                <img src="web/img/portfolio-2.jpg" alt="portfolio">
                                <div class="date">
                                    <span>12<br>Dec</span>
                                </div>

                                <div class="tag">
                                    <a href="#">Creative</a>
                                </div>
                            </div>

                            <div class="content">
                                <h4><a href="single-blog.html">The best tips of web design</a></h4>
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas in pulvinar neque.</p>
                            </div>
                            <a href="./single-blog" class="btn btn-primary">Read More <i class="fa fa-angle-double-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="single-blog-post">
                            <div class="thumb">
                                <img src="web/img/portfolio-3.jpg" alt="portfolio">
                                <div class="date">
                                    <span>12<br>Mar</span>
                                </div>

                                <div class="tag">
                                    <a href="#">Creative</a>
                                </div>
                            </div>

                            <div class="content">
                                <h4><a href="single-blog.html">The best tips of web design</a></h4>
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas in pulvinar neque.</p>
                            </div>
                            <a href="./single-blog" class="btn btn-primary">Read More <i class="fa fa-angle-double-right"></i></a>
                        </div>
                    </div> -->
                </div>
            </div>
        </section>
        <!-- End Blog Area -->