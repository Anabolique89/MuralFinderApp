<?php

include_once "header.php";
?>
<main>
    <!-- find  new walls section -->
    <section class="index-intro">
        <div class="index-intro-bg">

            <div class="wrapper">

                <div class="index-intro-c1">

                    <div class="video">
                        <img src="img/graphics/hologram.png" alt="fluidelement4" class="hero-graphic5">
                    </div>
                    <p class="cardss">If you are an artist or a stakeholder and you want to share a new painted wall with the world or for others to paint on, just add a pin on our map.
                        <br> <br>Just make sure the wall is a legal one and artists are allowed to paint there freely without any trouble. How to make sure? Check out our blogs for more info on how to tell the difference.
                        <!-- <br><br> Otherwise, if it's a permanent artwork, set the wall as illegal and people will visit your spot to see your amazing work! -->
                    </p>
                </div>
                <div class="index-intro-c2">
                    <h2>Publish New<br>Walls</h2>
                    <a href="map.php">ADD WALLS</a>
                </div>
            </div>
        </div>
    </section>
    <section class="all-walls">

        <h2>List of all walls</h2>
        <a href="addWall.php" class="btn-add-wall">Add New Wall</a>
        <br>
        <br>
        <table>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Address</th>
                <th>About</th>
                <th>action</th>
            </tr>

        </table>
    </section>
</main>
</body>

</html>