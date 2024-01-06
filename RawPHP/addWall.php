<?php

include_once "header.php";
?>
<section class="register-wall">
    <h3>Fill out this information to add a new wall</h3>
    <div class="container">
        <form action="../action_page.php"></form>
        <label for="name">Name</label>
        <input type="text" id="name" name="name" placeholder="Wall name...">

        <label for="name">Status</label>
        <input type="text" id="status" name="status" placeholder="Wall status...">

        <label for="name">Address</label>
        <input type="text" id="address" name="address" placeholder="Wall address...">

        <label for="name">About</label>
        <input type="text" id="about" name="about" placeholder="About...">
    </div>
</section>