<?php

session_start();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArtZoro Presentation Website</title>
    <link rel="stylesheet" href="style1.css">
    <link rel="stylesheet" href="footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
</head>

<body>
    <div class="homepage">
        <header class="main-header">

            <nav class="main-nav">
                <div class="navigation-container">
                    <div class="logo-container"><a href="webpage.php"><img class="logo" src="./img/LOGOBlack.png" alt="logo white"></a></div>
                    <ul class="menu-main">
                        <li><a href="webpage.php">HOME</a></li>
                        <li><a href="about.php">ABOUT</a></li>
                        <li><a href="map.php">MAP</a></li>
                        <li><a href="walls.php">WALLS</a></li>
                        <li><a href="community.php">COMMUNITY</a></li>
                        <li><a href="shops.php">SHOPS</a></li>
                        <li><a href="contactPage.php">CONTACT</a></li>
                    </ul>
                </div>
                <ul class="menu-member">
                    <?php
                    if (isset($_SESSION["userid"])) {
                    ?>
                        <li><a href="profile.php"><?php echo $_SESSION["username"]; ?></a></li>
                        <li><a href="includes/logout.inc.php" class="header-login-a">LOGOUT</a></li>
                    <?php
                    } else {
                    ?>
                        <li><a href="indexsignup.php">SIGN UP</a></li>
                        <li><a href="indexlogin.php" class="header-login-a ">LOGIN</a></li>
                    <?php
                    }
                    ?>
                </ul>
            </nav>
        </header>

        <main>
            <!--insert carousel hero section here-->
            <section class="index-intro">
                <div class="index-intro-bg">
                    <div class="video"><img src="img/graphics/fluidElement4.png" alt="fluidelement 1" class="hero-graphic1"></div>
                    <div class="wrapper">
                        <div class="index-intro-c1">

                            <!-- <div class="video"><img src="img/graphics/" alt="fluidelement 1" class="hero-graphic1"></div> -->
                            <i class="fa-brands fa-x-twitter icon"></i>
                            <i class="fa-brands fa-facebook icon"></i>
                            <i class="fa-brands fa-instagram icon"></i>
                            <p class="cardss p2">A platform that connects the urban art community worldwide and allows artists to explore new
                                terrain and expand their creative talents easily all the while meeting new people and sharing
                                new experiences with fellow artists. </p>
                        </div>
                        <div class="index-intro-c2">
                            <h2>Welcome to<br>ArtZoro</h2>
                            <a class="header-login-a" href="map.php">FIND WALLS</a>
                        </div>
                    </div>
                </div>
            </section>
            <section class="Artwork-gallery-main">

                <div class="cases-links">

                    <h2 class="Artworks-title">Artworks Feed</h2>
                    <div class="gallery-container">
                        <?php
                        include_once 'includes/dbh.inc.php';
                        $sql = "SELECT * FROM artwork ORDER BY OrderArtwork DESC";
                        $stmt = mysqli_stmt_init($conn);
                        if (!mysqli_stmt_prepare($stmt, $sql)) {
                            echo "SQL statement failed";
                        } else {
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '   <a href="#">
                <div class="image" style="background-image: url(img/artworks/' . $row["ImgFullNameArtwork"] . ');"></div>
                <h3>' . $row["TitleArtwork"] . '</h3>
                <p>' . $row["DescArtwork"] . '</p>
            </a> ';
                            }
                        }


                        ?>
                    </div>
                </div>
            </section>
            <!-- publish new walls section -->
            <section class="index-intro">
                <div class="index-intro-bg">

                    <div class="wrapper">

                        <div class="index-intro-c1">

                            <div class="video">
                                <img src="img/graphics/sfa1.png" alt="fluidelement 2" class="hero-graphic">
                            </div>
                            <p class="cardss">If you are an artist or a stakeholder and you want to share a new painted wall with the world for them to paint on or just explore in a certain location, we are here to make that happen. Just contact us and we will verify the information and make it happen! </p>
                        </div>
                        <div class="index-intro-c2">
                            <h2>Review<br>Walls</h2>
                            <a href="map.php">REVIEW WALL</a>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Subscribe or Register to Newsletter section here  -->
            <section class="newsletter">
                <div class="newsletter-bg">
                    <div class="wrapper">

                        <form action="newsletter.php">
                            <h2 class="Artworks-title">Newsletter</h2>
                            <p class="newsletter-p">Subscribe to our newsletter to receive updates and news.</p>
                            <div class="input-wrapper">
                                <input type="text" name="email" placeholder="Your email here..." class="input-text">
                            </div>
                            <a class="contact-btn header-login-a" href="map.php">SUBSCRIBE</a>
                        </form>
                    </div>
                </div>
            </section>
            <!-- publish new walls section 3 -->
            <section class="index-intro">
                <div class="index-intro-bg">

                    <div class="wrapper">
                        <div class="index-intro-c2">
                            <h2>Add New <br>Walls</h2>
                            <a href="map.php">ADD NEW WALL</a>
                        </div>
                        <div class="index-intro-c1">

                            <div class="video">
                                <img src="img/graphics/sdfc.png" alt="fluidelement 3" class="hero-graphic">
                            </div>
                            <p class="cardss">If you are a registered user and have any legal walls in mind don't hesitate to add them to our map. By doing this you are sharing with and helping
                                thousands of artists that are looking for places to paint or explore. </p>
                        </div>

                    </div>
                </div>
            </section>
            <!--wall feed or POST FEED goes here-->
            <!--contact form-->
            <!-- <h2 class="Artworks-title">HAVE A BURNING QUESTION?</h2> -->
            <section class="index-intro2">

                <div class="index-intro-c1 contact-form-text">
                    <h2 class="contact-title">Find more information here</h2>


                    <div class="video2">
                        <img src="img/graphics/xs.png" alt="fluidelement 3" class="hero-graphic contact-img">
                    </div>
                    <p class="contact-section-text">
                        Please don't hesitate to write to us if you have any suggestions about how we can improve and be of
                        even more help to the artistic community.
                        <br><br>
                        So you found a wall that is allegedly legal, but want to know for sure. If a spot is truly legal,
                        some basic web searches usually quickly confirm it. If not, do some research. Here is how to go about this. Find our blog
                        posts where we share all the information you need to know when searching for walls to paint or visit.
                        <br><br>
                        Still not satisfied? Write to us now and we will try to get back to you asap.
                    </p>

                    <!-- <a class="header-login-a contact-btn" href="contact.php">LEARN MORE</a> -->
                </div>
                <div class="contact-form-wrapper">


                    <br>
                    <form class="about-form" action="contactform.php" method="post">
                        <div class="input-wrapper2">
                            <input type="text" name="name" placeholder="Name" class="input-text2" required>
                        </div><br>
                        <div class="input-wrapper2">
                            <input type="text" name="mail" placeholder="Email" class="input-text2" required>
                        </div><br>
                        <div class="input-wrapper2">
                            <input type="text" name="subject" placeholder="Subject" class="input-text2" required>
                        </div>
                        <br>
                        <div class="input-wrapper2">
                            <textarea name="message" rows="10" cols="30" class="input-text2" placeholder="Your message here..."></textarea>
                        </div>
                        <br>
                        <button class="follow-btn" type="submit" name="submit">SEND</button>

                    </form>
                </div>

            </section>
            <!-- find  new walls section -->
            <section class="index-intro">
                <div class="index-intro-bg">

                    <div class="wrapper">

                        <div class="index-intro-c1">

                            <div class="video">
                                <img src="img/graphics/sd.png" alt="fluidelement4" class="hero-graphic">
                            </div>
                            <p class="cardss">If you are an artist or a stakeholder and you want to share a new painted wall with the world for them to paint on or just explore in a certain location, we are here to make that happen. Just contact us and we will verify the information and make it happen! </p>
                        </div>
                        <div class="index-intro-c2">
                            <h2>Find New<br>Walls</h2>
                            <a href="map.php">FIND WALLS</a>
                        </div>
                    </div>
                </div>
            </section>

            <div class="footer">
                <div class="bubbles">
                    <div class="bubble" style="--size:4.257872916809046rem; --distance:7.902498874020245rem; --position:60.656263684556194%; --time:2.070907867983733s; --delay:-2.376638478628786s;"></div>
                    <div class="bubble" style="--size:3.9956952456802473rem; --distance:9.09481360319122rem; --position:69.2250417542198%; --time:3.8339689970736295s; --delay:-3.088698339971494s;"></div>
                    <div class="bubble" style="--size:2.0185796633982287rem; --distance:7.969961621751381rem; --position:87.64853030514739%; --time:2.1283983531269715s; --delay:-2.465033051060404s;"></div>
                    <div class="bubble" style="--size:2.2120195151712556rem; --distance:9.367052498116081rem; --position:77.25751919976474%; --time:2.321678460803805s; --delay:-3.100421930290868s;"></div>
                    <div class="bubble" style="--size:2.0062049135129065rem; --distance:8.069063679931935rem; --position:-2.4347584434841596%; --time:3.293512616314893s; --delay:-2.168076446201073s;"></div>
                    <div class="bubble" style="--size:3.7517346144770345rem; --distance:8.196284728242627rem; --position:51.49823486472842%; --time:3.247171297961299s; --delay:-2.5302217891496803s;"></div>
                    <div class="bubble" style="--size:4.206031950793177rem; --distance:8.49902025073595rem; --position:-1.114347941530851%; --time:3.212394727202905s; --delay:-2.572291101694143s;"></div>
                    <div class="bubble" style="--size:3.8035967964778417rem; --distance:8.240742544226878rem; --position:41.11358821749536%; --time:2.8178390381779272s; --delay:-3.2950255034819036s;"></div>
                    <div class="bubble" style="--size:3.0889995263413494rem; --distance:7.2412436884021405rem; --position:91.02984331167904%; --time:3.7776330593239624s; --delay:-3.052993947862667s;"></div>
                    <div class="bubble" style="--size:4.298615437679785rem; --distance:8.034467503201071rem; --position:39.07110231439109%; --time:3.231315248877699s; --delay:-3.797603395872521s;"></div>
                    <div class="bubble" style="--size:4.001071921766673rem; --distance:8.156991444602886rem; --position:23.439499261564208%; --time:3.683747797880435s; --delay:-3.9187978209877277s;"></div>
                    <div class="bubble" style="--size:2.969924979009873rem; --distance:8.31222836757307rem; --position:46.641870958667035%; --time:3.7031004896107693s; --delay:-3.0412016255657384s;"></div>
                    <div class="bubble" style="--size:2.197228104155526rem; --distance:8.742341589849964rem; --position:-2.306629294394895%; --time:2.988462887018731s; --delay:-2.624730400007322s;"></div>
                    <div class="bubble" style="--size:2.808790688766618rem; --distance:8.291679826789267rem; --position:53.90260930581179%; --time:2.321034377142038s; --delay:-2.3610032929271663s;"></div>
                    <div class="bubble" style="--size:3.6057125961015624rem; --distance:6.940500000081764rem; --position:29.552288598727763%; --time:3.2413415785302835s; --delay:-3.834138665612256s;"></div>
                    <div class="bubble" style="--size:3.3010583657740478rem; --distance:8.76442195741091rem; --position:61.20876816629308%; --time:3.852956481117988s; --delay:-2.0492836101566385s;"></div>
                    <div class="bubble" style="--size:5.577962427621138rem; --distance:8.268990731834023rem; --position:84.67743409998447%; --time:2.1595974768810597s; --delay:-2.645151755123729s;"></div>
                    <div class="bubble" style="--size:2.6303112184305686rem; --distance:7.961807264576594rem; --position:3.5996280070731412%; --time:3.061297161815241s; --delay:-2.9350783568078405s;"></div>
                    <div class="bubble" style="--size:3.506396085027606rem; --distance:8.366943598989682rem; --position:47.2732593199443%; --time:3.429812276616159s; --delay:-2.440556040758213s;"></div>
                    <div class="bubble" style="--size:4.781615371623462rem; --distance:8.92989692443339rem; --position:19.185424018293432%; --time:3.8088627795323635s; --delay:-3.929746188923433s;"></div>
                    <div class="bubble" style="--size:4.725242016628999rem; --distance:7.170332945047493rem; --position:36.338218096229014%; --time:3.6434209277276133s; --delay:-2.3613626027305763s;"></div>
                    <div class="bubble" style="--size:5.142823318476264rem; --distance:8.556811937651911rem; --position:18.126090879774853%; --time:2.5774957832036125s; --delay:-3.604223416588701s;"></div>
                    <div class="bubble" style="--size:4.818501086660058rem; --distance:7.029849158340419rem; --position:39.6139041058844%; --time:2.270995144334852s; --delay:-3.6041382627430854s;"></div>
                    <div class="bubble" style="--size:3.6176021257207296rem; --distance:6.048234302119344rem; --position:75.35393429578438%; --time:3.429093136437456s; --delay:-2.655853835978373s;"></div>
                    <div class="bubble" style="--size:3.5414745951453934rem; --distance:8.694439227031815rem; --position:23.021826961336732%; --time:2.1718451908969794s; --delay:-2.3733931733388896s;"></div>
                    <div class="bubble" style="--size:5.593115746522803rem; --distance:7.422838004665246rem; --position:50.78801717162568%; --time:2.650165924694946s; --delay:-2.630757691633747s;"></div>
                    <div class="bubble" style="--size:3.9715588049297486rem; --distance:6.249587509217799rem; --position:62.48835900289937%; --time:3.214431056386629s; --delay:-2.0720162534488975s;"></div>
                    <div class="bubble" style="--size:2.964001983344657rem; --distance:9.555044097827153rem; --position:48.58251688867925%; --time:2.0995422783713633s; --delay:-2.3758450151261865s;"></div>
                    <div class="bubble" style="--size:4.481016938447985rem; --distance:6.68507410035356rem; --position:86.16948268219704%; --time:2.325077256569808s; --delay:-2.7970987816446002s;"></div>
                    <div class="bubble" style="--size:5.406173509355892rem; --distance:9.778163578745854rem; --position:19.9356839460231%; --time:2.47686862089618s; --delay:-2.319599911492675s;"></div>
                    <div class="bubble" style="--size:4.514298299392298rem; --distance:8.882880778079183rem; --position:82.1029961831135%; --time:2.5980833160964574s; --delay:-3.690318910781605s;"></div>
                    <div class="bubble" style="--size:4.604582880395099rem; --distance:9.344468908201486rem; --position:-3.2722222607338436%; --time:3.815100247375011s; --delay:-3.3287275491879313s;"></div>
                    <div class="bubble" style="--size:2.1690079133817726rem; --distance:8.1585326993922rem; --position:50.447363955631666%; --time:2.1357693917200296s; --delay:-3.377635747414306s;"></div>
                    <div class="bubble" style="--size:4.462197416711624rem; --distance:8.225552386936208rem; --position:89.31867195882332%; --time:2.8319995995296723s; --delay:-3.0758698510832896s;"></div>
                    <div class="bubble" style="--size:2.4300517892509736rem; --distance:9.56963990308118rem; --position:69.21707222289066%; --time:2.7188518241115127s; --delay:-2.966549049182075s;"></div>
                    <div class="bubble" style="--size:5.293526153062119rem; --distance:6.220720602889663rem; --position:34.60994784063359%; --time:2.68033409594391s; --delay:-2.962929591982407s;"></div>
                    <div class="bubble" style="--size:3.7824317780849803rem; --distance:8.134407762032364rem; --position:31.614818038541905%; --time:2.55076160638202s; --delay:-3.7769827695921125s;"></div>
                    <div class="bubble" style="--size:4.851749933384334rem; --distance:9.739642490875621rem; --position:20.80719277434242%; --time:3.3487893342842323s; --delay:-2.265857386102669s;"></div>
                    <div class="bubble" style="--size:4.82023822303889rem; --distance:9.797497938293724rem; --position:60.309665156313955%; --time:3.314681005384936s; --delay:-2.7944055442574647s;"></div>
                    <div class="bubble" style="--size:4.294394609779251rem; --distance:7.6616683993796215rem; --position:40.73369063275759%; --time:2.6371346281163697s; --delay:-2.4125323437007817s;"></div>
                    <div class="bubble" style="--size:3.554517280757639rem; --distance:8.961000401888839rem; --position:77.71548784961138%; --time:3.3254007889477313s; --delay:-3.894948627491432s;"></div>
                    <div class="bubble" style="--size:4.037621425954754rem; --distance:8.391775090182847rem; --position:4.272507001942971%; --time:2.978047357604089s; --delay:-2.9529495401028436s;"></div>
                    <div class="bubble" style="--size:3.9532868746337977rem; --distance:6.454477358484283rem; --position:82.32564014871923%; --time:2.399284576264187s; --delay:-3.450633825322768s;"></div>
                    <div class="bubble" style="--size:2.104369901296341rem; --distance:8.98797750514473rem; --position:10.1077170957591%; --time:3.2344441433157725s; --delay:-3.4087290852176233s;"></div>
                    <div class="bubble" style="--size:3.9399348915651933rem; --distance:8.271281885289287rem; --position:36.32859357955194%; --time:3.2508507533126143s; --delay:-2.6165627379626324s;"></div>
                    <div class="bubble" style="--size:5.765587862693477rem; --distance:6.776501923148573rem; --position:57.188300516637916%; --time:2.323243983471031s; --delay:-3.89453751906262s;"></div>
                    <div class="bubble" style="--size:2.208026809036536rem; --distance:7.744991094790178rem; --position:46.711701327514014%; --time:2.235137710282272s; --delay:-3.437942235084232s;"></div>
                    <div class="bubble" style="--size:3.295492151748686rem; --distance:8.697794568375393rem; --position:90.47076033771346%; --time:3.0717389543190206s; --delay:-2.19088251836363s;"></div>
                    <div class="bubble" style="--size:4.218636566293069rem; --distance:9.483770034091744rem; --position:64.6441499713443%; --time:2.2632752333943182s; --delay:-2.2257391505516426s;"></div>
                    <div class="bubble" style="--size:5.718512923762504rem; --distance:8.60518567195858rem; --position:24.714029571711546%; --time:3.014123531360236s; --delay:-2.624199519910684s;"></div>
                    <div class="bubble" style="--size:3.781320922384329rem; --distance:9.619998861649867rem; --position:36.44994683703726%; --time:3.250406779188891s; --delay:-2.8379394218634797s;"></div>
                    <div class="bubble" style="--size:2.598482096576375rem; --distance:8.031880764337462rem; --position:25.428673906732833%; --time:3.4422312940074398s; --delay:-3.324795912891684s;"></div>
                    <div class="bubble" style="--size:2.5045749610540593rem; --distance:8.383055242445254rem; --position:63.18660098686834%; --time:2.9057873392138407s; --delay:-2.68945591617696s;"></div>
                    <div class="bubble" style="--size:5.50641574682805rem; --distance:7.959131608333318rem; --position:103.16229392612748%; --time:3.218026903644445s; --delay:-3.8094319177695573s;"></div>
                    <div class="bubble" style="--size:5.135500291122706rem; --distance:7.964247857230174rem; --position:60.159158102228076%; --time:3.849520212679547s; --delay:-2.6269648478844325s;"></div>
                    <div class="bubble" style="--size:4.37916524097882rem; --distance:8.505388454275387rem; --position:35.55875230192703%; --time:2.373731331821328s; --delay:-3.3297631400921834s;"></div>
                    <div class="bubble" style="--size:4.3401274981962645rem; --distance:7.459527199345318rem; --position:36.39271767206916%; --time:2.4481592219115824s; --delay:-2.3518727906131462s;"></div>
                    <div class="bubble" style="--size:5.194450781029714rem; --distance:9.58878756469522rem; --position:0.42319369391978334%; --time:2.5141104841589414s; --delay:-2.573938135202264s;"></div>
                    <div class="bubble" style="--size:4.100839404015972rem; --distance:8.331476379567164rem; --position:15.903653552893108%; --time:2.4161557115882184s; --delay:-2.5934847128441874s;"></div>
                    <div class="bubble" style="--size:2.8365031689946356rem; --distance:6.446864789172536rem; --position:100.6445735544643%; --time:3.83781099549884s; --delay:-3.1840943185964345s;"></div>
                    <div class="bubble" style="--size:2.3033358748427792rem; --distance:7.841911288153833rem; --position:80.64515346830095%; --time:3.664919949763501s; --delay:-2.317247207124821s;"></div>
                    <div class="bubble" style="--size:4.772904539369304rem; --distance:6.018143461593496rem; --position:32.66311522425689%; --time:2.6317069589433917s; --delay:-3.1957217738393426s;"></div>
                    <div class="bubble" style="--size:3.8053501739346rem; --distance:9.300161945998223rem; --position:84.42071776401838%; --time:2.08026839178949s; --delay:-2.6587652098934136s;"></div>
                    <div class="bubble" style="--size:2.6850082683619396rem; --distance:7.1266633116044975rem; --position:61.34395686137037%; --time:2.26595281107827s; --delay:-2.2658063419279s;"></div>
                    <div class="bubble" style="--size:2.547134464687036rem; --distance:7.531211556859012rem; --position:83.32018275671733%; --time:3.766709194492974s; --delay:-3.563296989849851s;"></div>
                    <div class="bubble" style="--size:2.8068800449428393rem; --distance:6.808916942193031rem; --position:21.357885157283548%; --time:2.1332004364756836s; --delay:-2.643272040461178s;"></div>
                    <div class="bubble" style="--size:3.5733958034011035rem; --distance:7.288572975578775rem; --position:41.12018110802709%; --time:2.7380398282343803s; --delay:-2.0418918583933783s;"></div>
                    <div class="bubble" style="--size:3.418952468699536rem; --distance:9.197101866956205rem; --position:24.367554220044042%; --time:2.393506939158634s; --delay:-3.438712073611047s;"></div>
                    <div class="bubble" style="--size:4.421923669937821rem; --distance:7.276437019160281rem; --position:72.39046507198223%; --time:2.1401853452956208s; --delay:-3.0370891404646674s;"></div>
                    <div class="bubble" style="--size:4.9820080615441915rem; --distance:7.818810131408599rem; --position:45.55596734380162%; --time:2.13111116087909s; --delay:-2.9194886841066787s;"></div>
                    <div class="bubble" style="--size:5.690869110276699rem; --distance:6.452576885827192rem; --position:82.56274406492909%; --time:2.6136142254901795s; --delay:-3.8543940560126817s;"></div>
                    <div class="bubble" style="--size:2.795105250941136rem; --distance:9.527852651604473rem; --position:-2.3743949393587527%; --time:2.558622032711376s; --delay:-2.3892406079035995s;"></div>
                    <div class="bubble" style="--size:3.5255523173896863rem; --distance:8.419477363412497rem; --position:34.86523414544546%; --time:3.6888289732947324s; --delay:-3.8026958427637103s;"></div>
                    <div class="bubble" style="--size:3.2032892472898142rem; --distance:9.059410766590965rem; --position:10.5783049209812%; --time:2.5858313268429507s; --delay:-2.9939410849638275s;"></div>
                    <div class="bubble" style="--size:2.868253235389588rem; --distance:8.709414433332759rem; --position:62.864648463616405%; --time:2.390768768801133s; --delay:-2.2258867501431694s;"></div>
                    <div class="bubble" style="--size:3.396200879870803rem; --distance:8.770478704923523rem; --position:7.84125395855331%; --time:3.126277026271483s; --delay:-3.4873433594268475s;"></div>
                    <div class="bubble" style="--size:5.461896873597565rem; --distance:9.092244934360366rem; --position:4.8157008066845854%; --time:2.530584947049873s; --delay:-3.676928404338649s;"></div>
                    <div class="bubble" style="--size:4.127331788490566rem; --distance:9.661822655883986rem; --position:67.49036693520401%; --time:3.0392729897491155s; --delay:-2.974025325028814s;"></div>
                    <div class="bubble" style="--size:4.584830456934281rem; --distance:9.295556089222622rem; --position:36.05828967151363%; --time:3.668072783288186s; --delay:-2.47327297050948s;"></div>
                    <div class="bubble" style="--size:3.702250253223676rem; --distance:7.2826237724162945rem; --position:8.831053167856982%; --time:2.0649787136054814s; --delay:-2.810174301101773s;"></div>
                    <div class="bubble" style="--size:4.596540826951129rem; --distance:9.01272917037289rem; --position:92.75518505091142%; --time:2.656485668703289s; --delay:-3.321219472776989s;"></div>
                    <div class="bubble" style="--size:5.563852356836556rem; --distance:9.20697426834271rem; --position:75.9261958882071%; --time:2.4555822119362762s; --delay:-3.8841057933640903s;"></div>
                    <div class="bubble" style="--size:2.517713983257413rem; --distance:6.8791045701004485rem; --position:54.594312133026186%; --time:2.2280482498571947s; --delay:-3.9571887077944634s;"></div>
                    <div class="bubble" style="--size:4.676730299328283rem; --distance:8.462943934677783rem; --position:14.045432983658664%; --time:3.1613872399111225s; --delay:-3.043304023740722s;"></div>
                    <div class="bubble" style="--size:2.9755388187302527rem; --distance:8.359071815608885rem; --position:85.73135175321728%; --time:3.2437288415448755s; --delay:-2.581068760230769s;"></div>
                    <div class="bubble" style="--size:2.881144011787593rem; --distance:6.039365501143499rem; --position:48.797657592713236%; --time:2.4288793293049937s; --delay:-2.927033720068742s;"></div>
                    <div class="bubble" style="--size:4.596869898026486rem; --distance:9.941977107571049rem; --position:2.6268652150889693%; --time:2.471552090417611s; --delay:-2.2644550914324073s;"></div>
                    <div class="bubble" style="--size:3.6468231471370016rem; --distance:7.067029977146224rem; --position:84.41273913479284%; --time:2.9114184090479145s; --delay:-3.7429367263393734s;"></div>
                    <div class="bubble" style="--size:3.6141532901659517rem; --distance:9.706555048809035rem; --position:55.28939994269538%; --time:2.1927322092092933s; --delay:-3.9815500763034257s;"></div>
                    <div class="bubble" style="--size:3.0364082592781703rem; --distance:8.392414165507098rem; --position:32.30347285780663%; --time:2.611025454460469s; --delay:-2.825909004844115s;"></div>
                    <div class="bubble" style="--size:5.928489619729461rem; --distance:6.042900415504925rem; --position:73.05975371072309%; --time:3.8698490487989905s; --delay:-3.096914968083107s;"></div>
                    <div class="bubble" style="--size:4.536777379764235rem; --distance:8.595519319422692rem; --position:-0.4855267583092271%; --time:2.4280528075446517s; --delay:-2.194425140191829s;"></div>
                    <div class="bubble" style="--size:4.781878285363726rem; --distance:6.998266020872649rem; --position:55.692782444897375%; --time:2.351176886059809s; --delay:-2.632193074703852s;"></div>
                    <div class="bubble" style="--size:3.45916611231344rem; --distance:6.556224908799753rem; --position:37.21362499754878%; --time:2.497145054881888s; --delay:-2.947138820453237s;"></div>
                    <div class="bubble" style="--size:5.121394398370423rem; --distance:8.219591708756536rem; --position:101.04568625436323%; --time:3.2885522508828298s; --delay:-2.2232852715238405s;"></div>
                    <div class="bubble" style="--size:5.131199091891857rem; --distance:7.751398870543581rem; --position:-2.16168975469214%; --time:2.665901932580101s; --delay:-2.269254075194199s;"></div>
                    <div class="bubble" style="--size:5.669287498334944rem; --distance:9.007095353381988rem; --position:55.06989590681551%; --time:2.2683645280819245s; --delay:-3.17509061096739s;"></div>
                    <div class="bubble" style="--size:4.161369439935723rem; --distance:7.925102425843333rem; --position:-1.190588795431966%; --time:3.6979123751970575s; --delay:-2.6271945254962263s;"></div>
                    <div class="bubble" style="--size:3.738421392283925rem; --distance:9.104527938566452rem; --position:74.11764116975014%; --time:2.757087497478303s; --delay:-2.5046016204805897s;"></div>
                    <div class="bubble" style="--size:4.7102034335047795rem; --distance:9.429937940799295rem; --position:93.0477897045948%; --time:2.7637036726013107s; --delay:-3.6392885038891882s;"></div>
                    <div class="bubble" style="--size:3.0123963074183786rem; --distance:6.623681886041672rem; --position:50.90591174333023%; --time:3.61866221833723s; --delay:-3.481438145696506s;"></div>
                    <div class="bubble" style="--size:4.3896379065735rem; --distance:9.370019888260835rem; --position:-0.05748873435473989%; --time:3.8148713599769772s; --delay:-3.2025827114824748s;"></div>
                    <div class="bubble" style="--size:5.547342573555377rem; --distance:7.106457822443954rem; --position:-0.9185631049950409%; --time:3.9467410356791333s; --delay:-3.9225788174881324s;"></div>
                    <div class="bubble" style="--size:5.484938131149699rem; --distance:7.3366341754643rem; --position:53.35199066623426%; --time:2.630044820613904s; --delay:-3.5392978363074206s;"></div>
                    <div class="bubble" style="--size:3.946803050543851rem; --distance:6.413635559194528rem; --position:82.39121849015248%; --time:2.098224130960874s; --delay:-2.576808823404434s;"></div>
                    <div class="bubble" style="--size:5.810923916522696rem; --distance:9.69180552373399rem; --position:75.88214997078093%; --time:2.017572717993706s; --delay:-3.280030516775168s;"></div>
                    <div class="bubble" style="--size:3.5551108166212018rem; --distance:7.662980873492496rem; --position:8.958643420587185%; --time:2.4751592788385453s; --delay:-3.418158450311064s;"></div>
                    <div class="bubble" style="--size:5.483864947482845rem; --distance:7.095363224709449rem; --position:71.1204907843363%; --time:2.814524083861933s; --delay:-3.1697293043716193s;"></div>
                    <div class="bubble" style="--size:3.4362176173699535rem; --distance:8.103856222636708rem; --position:36.84709797576939%; --time:2.6038948693684354s; --delay:-2.750680517614607s;"></div>
                    <div class="bubble" style="--size:4.701590779680671rem; --distance:8.323623344817221rem; --position:33.314707504209636%; --time:3.0500682107733357s; --delay:-2.1506154053082054s;"></div>
                    <div class="bubble" style="--size:2.6238449565327917rem; --distance:6.561591191881477rem; --position:92.31050121893642%; --time:2.5277792234298047s; --delay:-2.4166549636764363s;"></div>
                    <div class="bubble" style="--size:5.0305555894597695rem; --distance:7.636652760433125rem; --position:89.31667439025378%; --time:2.1112431856006s; --delay:-3.67963862722927s;"></div>
                    <div class="bubble" style="--size:2.467088112898427rem; --distance:8.903134559908349rem; --position:86.21860483759652%; --time:3.9082683564793648s; --delay:-3.1427666417384406s;"></div>
                    <div class="bubble" style="--size:4.452887525724876rem; --distance:6.6314588316300735rem; --position:97.18266348778442%; --time:2.855219759807216s; --delay:-3.444790177367051s;"></div>
                    <div class="bubble" style="--size:2.6413502629347425rem; --distance:9.68870234110345rem; --position:30.563650757344085%; --time:3.635094985005856s; --delay:-2.3195239709768787s;"></div>
                    <div class="bubble" style="--size:5.579851680790552rem; --distance:9.405272521238473rem; --position:47.482172301733165%; --time:3.575678686042345s; --delay:-3.3252689943029345s;"></div>
                    <div class="bubble" style="--size:3.957137419828414rem; --distance:6.948370412586112rem; --position:27.317842796926982%; --time:3.322831599823241s; --delay:-3.850472463967217s;"></div>
                    <div class="bubble" style="--size:3.9369288654435515rem; --distance:8.602469536741282rem; --position:26.29218868147432%; --time:2.565473711338359s; --delay:-3.5813076001155895s;"></div>
                    <div class="bubble" style="--size:3.2713264586932462rem; --distance:8.381322390072334rem; --position:73.53549420766048%; --time:2.072402991256322s; --delay:-2.9115769371140754s;"></div>
                    <div class="bubble" style="--size:4.485671466209594rem; --distance:9.429996580342667rem; --position:35.10780466522526%; --time:3.4560072985937227s; --delay:-2.9277450011935677s;"></div>
                    <div class="bubble" style="--size:2.2954096169683194rem; --distance:9.038216423866906rem; --position:33.89189235387729%; --time:2.34978525732267s; --delay:-2.0848497120704175s;"></div>
                    <div class="bubble" style="--size:3.409631960435103rem; --distance:7.100615066675354rem; --position:18.235699914530667%; --time:2.1900561826220235s; --delay:-3.1365949504372117s;"></div>
                    <div class="bubble" style="--size:5.925577853026822rem; --distance:9.752444078093665rem; --position:47.51548154521782%; --time:3.549088310766995s; --delay:-2.225478798745388s;"></div>
                    <div class="bubble" style="--size:3.550602305051367rem; --distance:8.54270213349771rem; --position:27.97742136508343%; --time:2.4912476100934677s; --delay:-2.9135787549256174s;"></div>
                    <div class="bubble" style="--size:4.299656795778987rem; --distance:6.146316495731118rem; --position:18.750366500389102%; --time:2.7390852873266063s; --delay:-3.370115226613207s;"></div>
                    <div class="bubble" style="--size:4.9041386993532985rem; --distance:6.423419977741003rem; --position:3.6432872496711965%; --time:2.076123959132857s; --delay:-2.8801673474179297s;"></div>
                    <div class="bubble" style="--size:4.616695818265327rem; --distance:9.42400314842299rem; --position:82.89377522331614%; --time:3.5575797119584425s; --delay:-2.3412432973222983s;"></div>
                    <div class="bubble" style="--size:4.429000123460908rem; --distance:8.629065082609566rem; --position:4.298747369474851%; --time:2.2821344379125565s; --delay:-2.86005601257314s;"></div>
                </div>
                <div class="content">
                    <div>
                        <div><b>ArtZoro</b><a href="#">About</a><a href="#">Terms&Conditions</a><a href="#">Privacy Policy</a><a href="#">Community</a><a href="#">FAQ'S</a></div>
                        <div><b>Personal</b><a href="#">My Account</a><a href="#">Favourites</a><a href="#">Add Walls</a><a href="#">Contact</a></div>
                        <div><b>Community</b><a href="#">Wall Feed</a><a href="#">Stores</a><a href="#">Sponsors</a><a href="#">Events</a><a href="#">Register Event</a></div>
                        <div><b>Promotions</b><a href="#">Join Event</a><a href="#">Create Ads</a><a href="#"></a><a href="#"></a></div>
                    </div>
                    <div class="logo-container"><a href="webpage.php"><img class="logo" src="./img/LOGOBlack.png" alt="logo white"></a></div>
                </div>
            </div>
            <svg style="position: fixed; top: 100vh">
                <defs>
                    <filter id="blob">
                        <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur"></feGaussianBlur>
                        <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="blob"></feColorMatrix>
                        <!--feComposite(in="SourceGraphic" in2="blob" operator="atop") //After reviewing this after years I can't remember why I added this but it isn't necessary for the blob effect-->
                    </filter>
                </defs>
            </svg>
        </main>
    </div>
</body>

</html>