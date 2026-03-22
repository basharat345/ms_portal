<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mystery_shopper') {
    header("Location: index.php");
    exit;
}
require 'config/db.php';

$branch_id = $_GET['branch_id'] ?? 1;

// Verify this branch belongs to the logged in shopper!
$stmt = $pdo->prepare("SELECT b.*, p.name as project_name, p.script_file, p.audio_file FROM branches b JOIN projects p ON b.project_id = p.id WHERE b.id = ? AND b.assigned_shopper_id = ?");
$stmt->execute([$branch_id, $_SESSION['user_id']]);
$branch = $stmt->fetch();

if(!$branch) {
    die("Unauthorized access or invalid branch ID.");
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_report'])) {
    $video_path = ''; // This will act as our link OR our file path
    
    // 1. File Upload Priority
    if(isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $upload_dir = 'uploads/';
        if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $video_path = $upload_dir . time() . '_' . basename($_FILES['video']['name']);
        move_uploaded_file($_FILES['video']['tmp_name'], $video_path);
    } 
    // 2. Cloud Link Fallback
    elseif(!empty($_POST['video_url'])) {
        $video_path = trim($_POST['video_url']);
    }
    
    $form_data = json_encode(['q1' => $_POST['q1'] ?? '', 'q2' => $_POST['q2'] ?? '']);
    
    $stmt = $pdo->prepare("INSERT INTO submissions (branch_id, video_path, form_data) VALUES (?, ?, ?)");
    $stmt->execute([$branch_id, $video_path, $form_data]);
    
    $pdo->prepare("UPDATE branches SET status = 'submitted' WHERE id = ?")->execute([$branch_id]);
    
    echo "<script>alert('Success! Your report and evidence have been sent to the CS Team for review.'); window.location.href='my_assignments.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MRS Pro - Shopper Submission</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: var(--bg-color);
            background-image: radial-gradient(circle at top right, #3498DB, transparent 40%), radial-gradient(circle at bottom left, #2E86C1, transparent 40%);
            display: block;
            min-height: 100vh;
            padding-top: 40px;
        }
        .portal-container {
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
            padding: 0 20px 50px;
        }
        .shopper-panel {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .icon-logo {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
            box-shadow: 0 10px 20px rgba(46, 134, 193, 0.3);
        }
        .info-box {
            background: #F8FAFC;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary);
            font-size: 0.95rem;
        }
        .upload-area {
            border: 2px dashed rgba(46, 134, 193, 0.3);
            padding: 50px 20px;
            text-align: center;
            border-radius: 16px;
            background: rgba(46, 134, 193, 0.03);
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover { background: rgba(46, 134, 193, 0.08); }
    </style>
</head>
<body>
    
    <div class="portal-container">
        
        <div style="margin-bottom: 20px;">
            <a href="my_assignments.php" style="color: white; text-decoration: none; font-weight: 500;"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
        
        <div class="shopper-panel">
            <div style="text-align: center; margin-bottom: 30px;">
                <div class="icon-logo"><i class="fas fa-video"></i></div>
                <h2 style="font-size: 1.6rem;">Mystery Shopper Report</h2>
                <p style="color: var(--text-muted); font-size: 0.95rem;">Please review the instructions, complete your visit, and provide evidence.</p>
            </div>
            
            <?php if($branch): ?>
                <?php if($branch['status'] != 'assigned' && $branch['status'] != 'pending'): ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-main);">
                        <i class="fas fa-check-circle" style="font-size: 4rem; color: #27AE60; margin-bottom: 20px;"></i>
                        <h3>Thank You!</h3>
                        <p style="color: var(--text-muted);">Your report has been successfully submitted and is under review.</p>
                    </div>
                <?php else: ?>
                    <div class="info-box">
                        <h4 style="margin-bottom: 8px; color: var(--primary-dark); font-size: 1.1rem;"><?= htmlspecialchars($branch['project_name']) ?></h4>
                        <p style="margin-bottom: 4px;"><strong>Branch:</strong> <?= htmlspecialchars($branch['branch_name']) ?> (<?= htmlspecialchars($branch['branch_code']) ?>)</p>
                        <p style="margin:0;"><strong>Location:</strong> <?= htmlspecialchars($branch['city']) ?>, <?= htmlspecialchars($branch['region']) ?></p>
                    </div>
                    
                    <?php if($branch['script_file'] || $branch['audio_file']): ?>
                    <div style="background: rgba(41, 128, 185, 0.05); border: 1px solid rgba(41, 128, 185, 0.1); border-radius: 12px; padding: 20px; margin-bottom: 30px; text-align: center;">
                        <h4 style="margin-bottom: 12px; font-size: 1.05rem; color: var(--primary-dark);">Project Guidelines & Required Reading</h4>
                        <div style="display:flex; gap: 10px; justify-content:center; flex-wrap:wrap;">
                            <?php if($branch['script_file']): ?>
                                <a href="<?= htmlspecialchars($branch['script_file']) ?>" target="_blank" class="btn btn-secondary" style="font-size:0.85rem; background:white; border-color:#2980B9; color:#2980B9;"><i class="fas fa-file-word"></i> Read Script</a>
                            <?php endif; ?>
                            <?php if($branch['audio_file']): ?>
                                <a href="<?= htmlspecialchars($branch['audio_file']) ?>" target="_blank" class="btn btn-secondary" style="font-size:0.85rem; background:white; border-color:#E67E22; color:#E67E22;"><i class="fas fa-file-audio"></i> Listen to Audio</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="form-group" style="margin-bottom: 30px;">
                            <label style="font-size: 1rem; color: var(--text-main); font-weight: 600;">Evidence (Choose Video Upload OR Paste Link)</label>
                            
                            <div style="margin-top: 15px;">
                                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:10px;">Method 1: Upload File (Max size: 4GB)</p>
                                <div class="upload-area">
                                    <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--primary); margin-bottom: 15px;"></i>
                                    <p style="font-weight: 500; font-size: 1.05rem; color: var(--text-main);">Tap to select video</p>
                                    <input type="file" name="video" style="display:none;" id="videoUpload" accept="video/*" capture="environment">
                                </div>
                            </div>
                            
                            <div style="text-align: center; margin: 20px 0; position: relative;">
                                <hr style="border:0; border-top: 1px solid rgba(0,0,0,0.1);">
                                <span style="position: absolute; top: -10px; background: white; padding: 0 10px; font-weight: bold; color: var(--text-muted); left: 50%; transform: translateX(-50%); border-radius: 10px;">OR</span>
                            </div>
                            
                            <div>
                                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:10px;">Method 2: Paste Cloud Link (Google Drive, YouTube, OneDrive)</p>
                                <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/..." style="padding: 14px;">
                            </div>
                        </div>
                        
                        <h3 style="margin: 30px 0 20px; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 15px; font-size: 1.3rem;">Visit Questionnaire</h3>
                        
                        <div class="form-group" style="margin-bottom: 25px;">
                            <label style="font-size: 0.95rem; color: var(--text-main); font-weight: 600;">1. Was the store clean and organized upon entry?</label>
                            <select name="q1" class="form-control" style="margin-top: 10px; padding: 14px 16px;">
                                <option>Yes, exceptionally clean</option>
                                <option>Yes, acceptable</option>
                                <option>No, it was messy</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 25px;">
                            <label style="font-size: 0.95rem; color: var(--text-main); font-weight: 600;">2. Give a brief description of your interaction with the staff:</label>
                            <textarea name="q2" class="form-control" rows="5" style="margin-top: 10px; padding: 16px;" placeholder="Describe the greeting, service, and closing..."></textarea>
                        </div>
                        
                        <button type="submit" name="submit_report" class="btn btn-primary" style="width: 100%; margin-top: 10px; padding: 16px; font-size: 1.1rem; border-radius: 12px; box-shadow: 0 8px 25px rgba(46, 134, 193, 0.4);">
                            <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Submit Report
                        </button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-main); padding: 40px;">
                    <i class="fas fa-info-circle" style="font-size: 3rem; margin-bottom: 20px; color: var(--primary);"></i>
                    <h3 style="margin-bottom: 10px;">Ready to Start?</h3>
                    <p style="color: var(--text-muted); line-height: 1.6;">You need a valid branch assignment to submit a report.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        const uploadArea = document.querySelector('.upload-area');
        if(uploadArea) {
            uploadArea.addEventListener('click', () => {
                document.getElementById('videoUpload').click();
            });
            document.getElementById('videoUpload').addEventListener('change', function(e) {
                if(this.files.length > 0) {
                    const p = this.parentElement.querySelector('p');
                    p.innerHTML = '<span style="color:#27AE60"><i class="fas fa-check-circle"></i> ' + this.files[0].name + ' selected</span>';
                }
            });
        }
    </script>
</body>
</html>
