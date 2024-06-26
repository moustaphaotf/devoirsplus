<?php
session_start();
require 'config.php';

$titre = "Admin";

$devoir_a_montrer = "all";

if(isset($_GET['type-devoir']) && isset($devoirs[strtolower($_GET['type-devoir'])]))
{
    $devoir_a_montrer = strtolower($_GET['type-devoir']);
}

if(!isset($_SESSION["USER"]))
{
    header("Location: login.php");
}

require __DIR__ . DIRECTORY_SEPARATOR . 'dbconfig.php';

if ($devoir_a_montrer == "all") {
    $stmt = $db->prepare("SELECT DISTINCT e.nom, e.matricule, d1.devoir_type, d1.fichier, d1.date_envoi FROM etudiant e INNER JOIN devoir d1 ON e.id = d1.etudiant_id LEFT JOIN devoir d2 ON d1.etudiant_id = d2.etudiant_id AND d1.devoir_type = d2.devoir_type AND d1.date_envoi < d2.date_envoi WHERE d2.etudiant_id IS NULL ORDER BY d1.date_envoi DESC;");
    $stmt->execute();
}
else {
    $stmt = $db->prepare("SELECT DISTINCT e.nom, e.matricule, d1.dezvoir_type, d1.fichier, d1.date_envoi FROM etudiant e INNER JOIN devoir d1 ON e.id = d1.etudiant_id LEFT JOIN devoir d2 ON d1.etudiant_id = d2.etudiant_id AND d1.devoir_type = d2.devoir_type AND d1.date_envoi < d2.date_envoi WHERE d2.etudiant_id IS NULL AND d1.devoir_type = ? ORDER BY d1.date_envoi DESC;");
    $stmt->execute(array($devoir_a_montrer));
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?php require('partials/header.php') ?>

<div class="d-flex gap-2 align-items-center justify-content-end">
    <a href="index.php" class="btn text-primary">&larr; Retourner à l'Accueil</a>
    <form action="logout.php" method="post" class="my-2 d-flex align-items-center justify-content-end">
        <?=$_SESSION['USER']?>
        <button class="btn text-primary" type="submit">Se déconnecter</button>
    </form>
</div>

<form method="get" class="m-4">
    <div class="d-flex align-items-center gap-2 justify-content-end" >
        <label for="type-devoir">Filtrer</label>
        <div>
            <select class="form-select" name="type-devoir" id="type-devoir">
                <?php foreach($devoirs as $type => $devoir) : ?>
                    <option value="<?= $type ?>" <?= $devoir_a_montrer === $type ? "selected" : "" ?>><?= $devoir['nom'] ?></option>
                <?php endforeach ?>
            </select>
        </div>
    
        <div>
            <button class="btn btn-primary" type="submit">Rechercher</button>
        </div>
    </div>
</form>


<h1 class="my-4 text-center">Liste des dévoirs disponnibles</h1>

<div class="alert alert-secondary">
    <?php if($devoir_a_montrer !== 'all'): ?>
        <span>Devoirs Dispo: <span class="fw-bold"><?= $stmt->rowCount() ?>/178</span></span><br>
        <span>Taux: <span class="fw-bold"><?= round($stmt->rowCount() / 178, 2)*100 ?>%</span></span>
    <?php else: 
        $devCount = $devoirs;
        unset($devCount['all']);

        foreach($devCount as $type => $content) {
            $devCount[$type] = 0;
        }

        foreach($rows as $row) {
            $devCount[$row['devoir_type']]++;
        }

        echo "
            <table class='table table-secondary'>
                <tr>
                    <th>#</th>
                    <th>Type Dévoir</th>
                    <th>Taux de Soumission</th>
                </tr>
                ";
        $i = 1;
        foreach($devCount as $type => $count) {
            echo "<tr>";
            echo "<td>$i</td>";
            echo "<td>$type</td>";
            echo "<td class='fw-bold'>$count / 178 ( " . round($count / 178  * 100, 2) . "% ) </td>";
            echo "<tr>";
            $i++;
        }

        echo "</table>";

    endif ?>
</div>
    
    
<?php if($stmt->rowCount()): ?>
    <div class="table-responsive">
        <table class="table table-stripped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Matricule</th>
                    <th>Nom et prénoms</th>
                    <th>Dévoir</th>
                    <th>Date</th>
                    <th>Liens</th>
                </tr>
            </thead>
    
            <tbody>
                <?php $i = 1; ?>
                <?php foreach($rows as $row): ?>
                    <tr>
                        <td><?= $i ?></td>
                        <td><?= htmlentities($row['matricule']) ?></td>
                        <td><?= htmlentities($row['nom']) ?></td>
                        <td><?= htmlentities($row['devoir_type']) ?></td>
                        <td><?= htmlentities($row['date_envoi']) ?></td>
                        <td>
                            <a class="btn text-primary" target="_blank" href="<?= $shoulShowLink ? ('https://docs.google.com/viewer?url=' . $baseUrl . '/' . $upload_dir . '/' . $row['fichier']) : $baseUrl ?>">Voir &rarr; </a>
                        </td>
                    </tr>
                    <?php $i++ ?>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <div class="alert alert-primary" role="alert">
        Aucun devoir pour l'instant !
    </div>
<?php endif ?>

<?php require('partials/footer.php') ?>