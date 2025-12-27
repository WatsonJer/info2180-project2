<?php
require_once '../database/config.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: ../dashboard/dashboard.php');
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare('SELECT c.*, u.firstname AS creator_firstname, u.lastname AS creator_lastname FROM Contacts c LEFT JOIN Users u ON c.created_by = u.id WHERE c.id = ?');
$stmt->execute([$id]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$contact) {
    header('Location: ../dashboard/dashboard.php');
    exit();
}

function e($s) { return htmlspecialchars($s); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Contact - Dolphin CRM</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <?php include '../dashboard/header.php'; ?>
    <div class="main-container">
        <?php include '../dashboard/sidebar.php'; ?>
        <div class="content">
            <div class="content-header">
                <h2>Contact Details</h2>
                <div>
                    <?php if ($contact['assigned_to'] != $_SESSION['user_id']): ?>
                        <button id="assignBtn" class="btn btn-assign">Assign to Me</button>
                    <?php endif; ?>
                    <button id="switchTypeBtn" class="btn btn-switch">
                        <?= $contact['type'] === 'support' ? 'Switch to Sales Lead' : 'Switch to Support' ?>
                    </button>               
                    <a href="../dashboard/dashboard.php" class="btn-link">Back</a>
                </div>
            </div>

            <div class="form-horizontal">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <div><?= e($contact['title'] . ' ' . $contact['firstname'] . ' ' . $contact['lastname']) ?></div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <div><?= e($contact['email']) ?></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Telephone</label>
                        <div><?= e($contact['telephone']) ?></div>
                    </div>
                    <div class="form-group">
                        <label>Company</label>
                        <div><?= e($contact['company']) ?></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Type</label>
                        <div id="contactType"><?= e($contact['type'] === 'salesLead' ? 'Sales Lead' : ($contact['type'] === 'support' ? 'Support' : $contact['type'])) ?></div>
                    </div>
                    <div class="form-group">
                        <label>Assigned To</label>
                        <div id="assignedTo">
                            <?php if ($contact['assigned_to']): ?>
                                <?php
                                    $stmt2 = $conn->prepare('SELECT firstname, lastname FROM Users WHERE id = ?');
                                    $stmt2->execute([$contact['assigned_to']]);
                                    $ass = $stmt2->fetch(PDO::FETCH_ASSOC);
                                    echo $ass ? e($ass['firstname'] . ' ' . $ass['lastname']) : '—';
                                ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Created By</label>
                    <div><?= e($contact['creator_firstname'] . ' ' . $contact['creator_lastname']) ?></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Created At</label>
                        <div><?= e($contact['created_at']) ?></div>
                    </div>
                    <div class="form-group">
                        <label>Updated At</label>
                        <div id="updatedAt"><?= e($contact['updated_at']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="notes-section">
                <h3>Notes</h3>
                
                <div id="notesList">
                    <?php
                    // Fetch notes for this contact
                    $stmt = $conn->prepare('
                        SELECT n.*, u.firstname, u.lastname 
                        FROM Notes n 
                        JOIN Users u ON n.created_by = u.id 
                        WHERE n.contact_id = ? 
                        ORDER BY n.created_at DESC
                    ');
                    $stmt->execute([$id]);
                    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($notes):
                        foreach ($notes as $note):
                    ?>
                        <div class="note-item">
                            <div class="note-header">
                                <strong><?= e($note['firstname'] . ' ' . $note['lastname']) ?></strong>
                            </div>
                            <div class="note-comment">
                                <?= nl2br(e($note['comment'])) ?>
                            </div>
                            <div class="note-date">
                                <?= e(date('F j, Y \a\t g:ia', strtotime($note['created_at']))) ?>
                            </div>
                        </div>
                    <?php
                        endforeach;
                    else:
                    ?>
                        <p class="no-notes">No notes yet.</p>
                    <?php endif; ?>
                </div>

                <div class="add-note-section">
                    <h4>Add a note about <?= e($contact['firstname']) ?></h4>
                    <textarea id="noteComment" placeholder="Enter details here" rows="4"></textarea>
                    <button id="addNoteBtn" class="btn">Add Note</button>
                </div>
            </div>

        </div>
    </div>

    <script>
    const contactId = <?= (int)$id ?>;

    //Assign Button
    const assignBtn = document.getElementById('assignBtn');

    if (assignBtn) {
        assignBtn.addEventListener('click', function () {

            assignBtn.disabled = true;

            fetch('updateContact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=assign&id=' + encodeURIComponent(contactId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('assignedTo').textContent = data.assigned_to;
                    document.getElementById('updatedAt').textContent = data.updated_at;
                    assignBtn.remove(); // instantly remove button
                } else {
                    alert(data.error || 'Failed to assign contact');
                    assignBtn.disabled = false;
                }
            })
            .catch(() => {
                alert('Request failed');
                assignBtn.disabled = false;
            });
        });
    }

    //Switch Button
    const switchTypeBtn = document.getElementById('switchTypeBtn');

    if (switchTypeBtn) {
        switchTypeBtn.addEventListener('click', function () {

            switchTypeBtn.disabled = true;

            fetch('updateContact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=switch_type&id=' + encodeURIComponent(contactId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('contactType').textContent = data.type_display;
                    document.getElementById('updatedAt').textContent = data.updated_at;
                    switchTypeBtn.textContent = data.button_text;
                } else {
                    alert(data.error || 'Failed to switch type');
                }
            })
            .catch(() => alert('Request failed'))
            .finally(() => {
                switchTypeBtn.disabled = false;
            });
        });
    }

    // Add Note functionality
    const addNoteBtn = document.getElementById('addNoteBtn');
    const noteComment = document.getElementById('noteComment');

    if (addNoteBtn) {
        addNoteBtn.addEventListener('click', function () {
            const comment = noteComment.value.trim();
            
            if (!comment) {
                alert('Please enter a note');
                return;
            }

            addNoteBtn.disabled = true;

            fetch('updateContact.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=add_note&id=' + encodeURIComponent(contactId) + '&comment=' + encodeURIComponent(comment)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear textarea
                    noteComment.value = '';
                    
                    // Update the notes list
                    const notesList = document.getElementById('notesList');
                    
                    // Remove "no notes" message if it exists
                    const noNotes = notesList.querySelector('.no-notes');
                    if (noNotes) {
                        noNotes.remove();
                    }
                    
                    // Add new note at the top
                    const newNote = document.createElement('div');
                    newNote.className = 'note-item';
                    newNote.innerHTML = `
                        <div class="note-header">
                            <strong>${data.user_name}</strong>
                        </div>
                        <div class="note-comment">
                            ${data.comment.replace(/\n/g, '<br>')}
                        </div>
                        <div class="note-date">
                            ${data.created_at}
                        </div>
                    `;
                    notesList.insertBefore(newNote, notesList.firstChild);
                    
                    // Update the updated_at timestamp
                    document.getElementById('updatedAt').textContent = data.contact_updated_at;
                } else {
                    alert(data.error || 'Failed to add note');
                }
            })
            .catch(() => alert('Request failed'))
            .finally(() => {
                addNoteBtn.disabled = false;
            });
        });
    }
    </script>

</body>
</html>