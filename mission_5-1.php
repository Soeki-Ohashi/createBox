<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission5-1</title>
</head>
<body>
    名前とコメントを入力してください。課題を完了した場合は「完成」(括弧なし)と入力してください。<br>
    <!-- データベースに接続-->
    <?PHP
    $dsn = 'データベース名';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING)); 
    
    //テーブルの作成
    $sql = "CREATE TABLE IF NOT EXISTS dataTable"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name char(32),"
    . "comment TEXT,"
    . "date TEXT,"
    . "passcode char(16)"
    .");";
    $stmt = $pdo->query($sql);
    
    ?>
    
    <?php
        if(isset($_POST["edit"] ,$_POST["editPass"])) {
        //入力受け取り。
        $number = $_POST["edit"];
        $passcode = $_POST["editPass"];
            //エラー防止。
            if(strlen($number) && strlen($passcode)){
                //カウンターの設置。
                $passChecker = 0;
                $numChecker = 0;
                
                //管理者機能
                if(($number == "administrator") && ($passcode == "0627")){
                    // データレコードの抽出
                    $sql = 'SELECT * FROM dataTable';
                    $stmt = $pdo->query($sql);
                    $results = $stmt->fetchAll();
                    foreach ($results as $row){
                        echo $row['id'].'.';
                        echo $row['name'].':';
                        echo $row['comment'].':';
                        echo $row['date'].'<';
                        echo $row['passcode'].'><br>';
                    }
                    $mode = "editor";
                } else{
                
                    // データレコードの抽出
                    $sql = 'SELECT * FROM dataTable';
                    $stmt = $pdo->query($sql);
                    $results = $stmt->fetchAll();
                    foreach ($results as $row){
                    if($row['id'] == $number){
                        if($row['passcode'] == $passcode){
                            $passChecker ++;
                        } else{
                                echo "パスワードが間違っています。<br>";
                                $numChecker ++;
                            }
                        } else{
                            ;
                        }
                    }
                
                    if($passChecker > 0){
                        // データレコードの抽出
                        $sql = 'SELECT * FROM dataTable WHERE id = ' . $number . '';
                        $stmt = $pdo->query($sql);
                        $results = $stmt->fetchAll();
                        foreach ($results as $row){
                        //$rowの中にはテーブルのカラム名が入る
                        $printName = $row['name'];
                        $printComment = $row['comment'];
                        }
                        //編集モードに設定。
                        $mode = "$number";
                    } else if($numChecker == 0){
                        echo "番号が間違っています。<br>";
                    }
                }
            } else if(strlen($number)){
                echo "パスワードが入力されていません。";
            } else if(strlen($passcode)){
                echo "コメント番号が入力されていません。";
            } else{
                echo "入力してください。";
            }                
        } else {
            ;
        }

    
    //テキストの保存
    //エラー防止
    if(isset($_POST["name"], $_POST["comment"])){
        //名前とコメントを取得。
        $inputName = trim($_POST["name"], " ");
        $inputComment = trim($_POST["comment"], " ");
        //コメント取得時の時間を取得。
        $inputDate = date("Y/m/d(D,M) H:i:s");
        //パスワード生成。
        $inputKey = sprintf('%04d',mt_rand(0,9999));
        
        $checker = 0;
        
        //コメントと名前双方入力されている場合。
        if(strlen($inputName) && strlen($inputComment)){
            //新規
            if($_POST["hidden"] === "new"){
                //データの登録
                $sql = $pdo -> prepare("INSERT INTO dataTable (name, comment,date,passcode) VALUES (:name, :comment, :date, :passcode)");
                $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                $sql -> bindParam(':date', $date , PDO::PARAM_STR);
                $sql -> bindParam(':passcode', $passcode, PDO::PARAM_STR);
                $name = $inputName;
                $comment = $inputComment;
                $date = $inputDate;
                $passcode = $inputKey;
                $sql -> execute();
    
                //コメントが『完成』とある時。
                if($inputComment == "完成"){
                    echo "congratulation!<br>";
                }
                //その他のコメントの時。
                else{
                    echo "$inputName" . "." . "$inputComment" . "<br>";
                }
                echo "あなたのpasscodeは" . "$passcode" . "です。<br>これはコメントの削除・編集の際必要となります。<br>";
            }
            //管理者
            else if($_POST["hidden"] === "editor"){
                //削除権限
                if($inputComment == "削除"){
                     //データレコードの削除
                    $id = $inputName;
                    $sql = 'delete from dataTable where id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();
                    echo "正常に削除できました。";
                    $mode = "new";
                } else{
                    //編集権限
                    // データレコードの抽出
                    $sql = 'SELECT * FROM dataTable';
                    $stmt = $pdo->query($sql);
                    $results = $stmt->fetchAll();
                    foreach ($results as $row){
                        if($row['id'] == $inputName){
                            $checker ++;
                        } else{
                            ;
                        }
                    }
                    
                    if($checker != 0){
                        $id = $inputName;
                        $sql = 'UPDATE dataTable SET comment=:comment WHERE id=:id';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':comment', $inputComment, PDO::PARAM_STR);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();
                        echo "正常に変更できました。";
                        $mode = "new";
                    } else{
                        echo "編集元のデータが見つかりません";
                    }
                }
            }
                //編集
            else{
                //編集機能
                //データレコードの書き換え
                $id = $_POST["hidden"]; //変更する投稿番号
                $sql = 'UPDATE dataTable SET name=:name,comment=:comment WHERE id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $inputName, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $inputComment, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $mode = "new";
                
                echo "正常に変更されました<br>";
            }
        }
        //コメント抜けの場合。            
        elseif(strlen($inputName)){
            echo "comment抜け<br>";
        }
        //名前抜けの場合            
        elseif(strlen($inputComment)){
            echo "name抜け<br>";
        } 
        //双方ブランクの場合。            
        else{
            echo "入力して下さい<br>";
        }
    } else{
        ; //動作なし
    }
    
    if(isset($_POST["archive"])){
        // データレコードの抽出
        $sql = 'SELECT * FROM dataTable';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            echo $row['id'].'.';
            echo $row['name'].':';
            echo $row['comment'].':';
            echo $row['date'].'<br>';
        }
    } else{
        ;
    }
   
    //コメントの削除。
    //初期エラーの防止。
    if(isset($_POST["delete"] ,$_POST["deletePass"])) {
        //入力受け取り。
        $number = $_POST["delete"];
        $passcode = $_POST["deletePass"];
        $passChecker = 0;
        $numChecker = 0;
        //エラー防止。
        if(strlen($number) && strlen($passcode)){
            // データレコードの抽出
            $sql = 'SELECT * FROM dataTable';
            $stmt = $pdo->query($sql);
            $results = $stmt->fetchAll();
            foreach ($results as $row){
                if($row['id'] == $number){
                    if($row['passcode'] == $passcode){
                        $passChecker ++;
                    } else{
                        echo "パスワードが間違っています。<br>";
                        $numChecker ++;
                    }
                } else{
                    ;
                }
            }
            if($passChecker > 0){
                //データレコードの削除
                $id = $number;
                $sql = 'delete from dataTable where id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                echo "正常に削除できました。";
            } else if($numChecker == 0){
                echo "番号が間違っています。<br>";
            }
        } else if(strlen($number)){
            echo "パスワードが入力されていません。";
        } else if(strlen($passcode)){
            echo "コメント番号が入力されていません。";
        } else{
            echo "入力してください。";
        }
    } else {
        ;
    }

    ?>
    <!-- 入力ボックス内のコメント&mode編集-->
    <?php
        if(isset($printName) && isset($printComment)){
            ;
        } else{
            $printName = "";
            $printComment = "";
        }
        
        if(isset($mode)){
            ;
        } else{
            $mode = "new";
        }
    ?>
    <!-- 送信フォームの作成。 -->
    <form  method="post">
        <input type="text" name="name" value="<?php echo "$printName"; ?>" placeholder="Please input your name.">
        <input type="text" name="comment" value="<?php echo "$printComment"; ?>" placeholder="Please input a comment.">
        <input type="hidden" name="hidden" value="<?php echo "$mode"; ?>">
        <input type="submit" value="送信">
    </form>

    <!-- 履歴の削除 -->
    <form  method="post">
        <input type="text" name = "delete" size="6" placeholder="削除番号">
        <input type="text" name = "deletePass" size = "6" placeholder="パスワード">       
        <input type="submit" value="送信">
    </form>
    
    <!--　過去コメントの編集-->
    <form  method="post">
        <input type="text" name = "edit" size="6" placeholder="編集番号">
        <input type="text" name = "editPass" size = "6" placeholder="パスワード">       
        <input type="submit" value="送信">
    </form>
    
    <!-- 履歴の確認 -->
    <form  method="post">
        <button type="submit" name="archive">過去のコメントを表示</button>
    <br>

</body>
</html>