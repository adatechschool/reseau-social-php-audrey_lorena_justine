<?php
    session_start();
    include('_header.php');
    include('_database.php');
    include('_loggedUserQuery.php');
?>

<!doctype html>
<html>
    <head>
        <title>ReSoC - Flux</title>         
    </head>
    <body>
        <div id="wrapper">

            <aside>
                <img src="<?php echo $user['pictures']?>" alt="Portrait de l'utilisatrice"/>
                
                <section>
                    <h3>Bonjour <?php echo $user['alias'] ?> !<br>
                    Bienvenue sur votre feed.</h3>
                    <p>Vous trouverez ici tous les messages de vos amis Pandas !</p>
                </section>
            </aside>
            
            <main>
            <?php 
                // ?
                $enCoursDeTraitement = isset($_POST['Like']);
                    if ($enCoursDeTraitement)
                    {   
                        $new_like = $_POST['Like'];
                        $new_like = $mysqli->real_escape_string($new_like);  
                                        
                        $addNewLike = "INSERT INTO likes "
                            . "(id, user_id, post_id) "
                            . "VALUES (NULL, "
                            . $loggedUserId .", "
                            . $_GET['post_id'] ." );"
                            ;
                        $mysqli->query($addNewLike);
                        header("location:feed.php?user_id=" . $loggedUserId);
                        exit();
                    }
                
                    $enCoursDeTraitement = isset($_POST['Unlike']);
                    if ($enCoursDeTraitement)
                    {   
                        $deleting_like = $_POST['Unlike'];
                        $deleting_like = $mysqli->real_escape_string($deleting_like);  
                    
                        $deleteLiked= "DELETE FROM likes 
                        WHERE user_id= '" . $loggedUserId . "' AND post_id= '" . $_GET['post_id'] ."' ";
                        $mysqli->query($deleteLiked);     
                        header("location:feed.php?user_id=" . $loggedUserId);
                        exit();    
                    }
            ?>
                <?php
                // on r??cup??re toutes les infos qui concernent les messages post??s par les personnes auxquelles le user est abonn??
                // id du post, date de cr??ation, auteur, tags, etc. rang??s par ordre descendant (date)
                $feedPostsQuery = "
                    SELECT posts.content,
                    posts.created,
                    posts.id as post_id,
                    users.alias as author_name, 
                    users.id as user_id, 
                    COUNT(DISTINCT likes.id) as like_number,  
                    GROUP_CONCAT(DISTINCT tags.label) AS taglist,
                    GROUP_CONCAT(DISTINCT tags.id) AS tagidlist 
                    FROM followers 
                    JOIN users ON users.id=followers.followed_user_id
                    JOIN posts ON posts.user_id=users.id
                    LEFT JOIN posts_tags ON posts.id = posts_tags.post_id  
                    LEFT JOIN tags       ON posts_tags.tag_id  = tags.id 
                    LEFT JOIN likes      ON likes.post_id  = posts.id 
                    WHERE followers.following_user_id='$loggedUserId' 
                    GROUP BY posts.id
                    ORDER BY posts.created DESC  
                    ";
                
                // on envoie la requ??te
                $feedPostsQueryInfo = $mysqli->query($feedPostsQuery);
                // check du fonctionnement de la requ??te
                if ( ! $feedPostsQueryInfo)
                {
                    echo("??chec de la requ??te : " . $mysqli->error);
                }

                // r??cup??ration des infos dans un tableau + boucle qui permet de les r??injecter dans le HTML ; ne pas confondre avec $_POST
                while ($post = $feedPostsQueryInfo->fetch_assoc())
                {
                ?>   
        
                <article>
                    <h3>
                        <time datetime='2020-02-01 11:12:13' ><?php echo $post['created'] ?></time>
                    </h3>
                    
                    <address>par <a href="wall.php?user_id=<?php echo $post['user_id'] ?>"><?php echo $post['author_name'] ?></a></address>
                   
                    <div>
                        <p><?php echo $post['content']?></p>
                    </div>                                            
                    
                    <footer>
                        <small>
                            <?php 
                                // d??finition des param??tres de la requ??te
                                $likeStatus = "SELECT * FROM likes WHERE user_id= '" . $loggedUserId . "' AND post_id='" . $post['post_id'] ."' ";
                                // envoi de la requ??te
                                $likeStatusInfos = $mysqli->query($likeStatus);
                                // r??cup??ration des infos dans un tableau ordonn?? et stockage dans une variable
                                $isLiked = $likeStatusInfos->fetch_assoc();

                                // on v??rifie si la variable existe + que isLiked est vide
                                if (isset($loggedUserId) and !$isLiked) { ?>
                                    <form action="feed.php?post_id=<?php echo $post['post_id'] ?>" method="post">
                                        <input type='submit' name="Like" value="????">
                                        <?php echo $post['like_number'] ?> 
                                    </form>
                            <?php
                                // lorsque isLiked existe :
                                } else if ($isLiked) { ?>
                                    <form action="feed.php?post_id=<?php echo $post['post_id'] ?>" method="post">
                                        <input type='submit' name="Unlike" value="????">
                                        <?php echo $post['like_number'] ?> 
                                    </form>
                            <?php } ?>
                        </small>
                        <?php include('_tags.php'); ?>
                    </footer>
                </article>
                <?php } ?>
            </main>
        </div>
    </body>
</html>
