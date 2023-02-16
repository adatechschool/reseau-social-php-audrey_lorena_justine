<?php
session_start();
?>
<!doctype html>
<html>
    <head>
        <?php include('_header.php'); ?>
        <title>ReSoC - Flux</title>         

    </head>
    <body>
        <div id="wrapper">
            <?php
            include('database.php');
            $userId = intval($_GET['user_id']);
            ?>

            <aside>
                <?php
                $laQuestionEnSql = "SELECT * FROM `users` WHERE id= '$userId' ";
                $lesInformations = $mysqli->query($laQuestionEnSql);
                $user = $lesInformations->fetch_assoc();
                ?>

                <img src="user.jpg" alt="Portrait de l'utilisatrice"/>
                <section>
                    <h3>Présentation</h3>
                    <p>Sur cette page vous trouverez tous les message des utilisatrices
                        auxquel est abonnée l'utilisatrice <?php echo $user['alias'] ?>
                        (n° <?php echo $userId ?>)
                    </p>
                </section>
            </aside>

            <main>
                <?php
                $laQuestionEnSql = "
                    SELECT posts.content,
                    posts.created,
                    users.alias as author_name, 
                    users.id as user_id, 
                    count(likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist,
                    GROUP_CONCAT(DISTINCT tags.id) AS tagidlist 
                    FROM followers 
                    JOIN users ON users.id=followers.followed_user_id
                    JOIN posts ON posts.user_id=users.id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE followers.following_user_id='$userId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                    ";
                
                    $lesInformations = $mysqli->query($laQuestionEnSql);
                if ( ! $lesInformations)
                {
                    echo("Échec de la requete : " . $mysqli->error);
                }

                while ($post = $lesInformations->fetch_assoc())
                {
                ?>   
        
                <article>
                    <h3>
                        <time datetime='2020-02-01 11:12:13' ><?php echo $post['created'] ?></time>
                    </h3>
                    <address>par <a href="wall.php?user_id=<?php echo $post ['user_id'] ?>"><?php echo $post['author_name'] ?></a></address>
                   
                    <div>
                        <p><?php echo $post['content']?></p>
                    </div>                                            
                    <footer>
                        <small>♥ <?php echo $post['like_number']?></small>
                        <?php include('_tags.php'); ?>
                    </footer>
                </article>
                <?php } ?>
            </main>
        </div>
    </body>
</html>
