<h1 class="center color4">CONTACTEZ-NOUS</h1>
      <form action="front.php?action=contact#news" method="post" class="flex pad">
        <div class="w2">
            <?php if(isset($message['nom_contact'])){echo $message['nom_contact'];} ?>
            <input id="nom" class="w1 <?php if(isset($color_champ['nom_contact'])){echo $color_champ['nom_contact'];} ?>" placeholder="Nom [obligatoire]" type="text" name="nom_contact" value="<?php if(isset($_POST['nom_contact'])){echo $_POST['nom_contact'];} ?>">
            <label for="nom">Nom</label>
          
        </div>

        <div class="w2">
          <input id="prenom" class="w1" placeholder="Prénom [facultatif]" type="text" name="prenom_contact" value="<?php if(isset($_POST['prenom_contact'])){echo $_POST['prenom_contact'];} ?>">
          <label for="prenom">Prénom</label>
        </div>

        <div class="w1">
            <?php if(isset($message['email_contact'])){echo $message['email_contact'];} ?>
            <input id="email" class="w1 <?php if(isset($color_champ['message_contact'])){echo $color_champ['message_contact'];} ?>" placeholder="Email [obligatoire]" type="email" name="email_contact" value="<?php if(isset($_POST['email_contact'])){echo $_POST['email_contact'];} ?>">
            <label for="email">Email</label>
        </div>

        <div class="w1">
            <?php if(isset($message['message_contact'])){echo $message['message_contact'];} ?>
            <textarea class="w1 <?php if(isset($color_champ['message_contact'])){echo $color_champ['message_contact'];} ?>" id="message" placeholder="Message [obligatoire]" name="message_contact"><?php if(isset($_POST['message_contact'])){echo $_POST['message_contact'];} ?></textarea>
            <label for="message">Message</label>
        </div>

        <input class="bgcolor3" type="submit" name="submit" value="SEND">
</form>