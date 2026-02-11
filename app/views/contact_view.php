<?php
$pageTitle = 'Contact Us';
$pageSubtitle = 'Get in touch with our support team';

require_once __DIR__ . '/partials/header.php'; 
?>

<style>
.info-card-header {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.info-guide-wrapper {
    margin-bottom: 2.5rem;
    padding: 0 5px;
}
.info-guide-content {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.faq-item {
    padding: 20px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}
.faq-item:hover {
    border-color: #f59e0b;
    background: #fff;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.modal-icon-circle {
    width: 80px; height: 80px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.5rem; font-size: 2.5rem;
}
.modal-success { 
    background: #ecfdf5; color: #10b981; border: 4px solid #fff; box-shadow: 0 4px 10px rgba(16,185,129,0.1); 
}

.modal-error { 
    background: #fef2f2; color: #ef4444; border: 4px solid #fff; box-shadow: 0 4px 10px rgba(239,68,68,0.1); 
    }
</style>

<div class="main-body">
    <div class="info-card-header">
        <div style="background: rgba(255,255,255,0.1); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="fa-solid fa-headset"></i>
        </div>
        <div>
            <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #ffffff !important;">Support & Assistance</h2>
            <p style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem; color: #ffffff !important;">Get in touch with our technical team for system concerns or inquiries.</p>
        </div>
    </div>

    <div class="info-guide-wrapper">
        <div class="info-guide-content">
            <div style="background: #f1f5f9; color: #64748b; width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div style="flex: 1;">
                <p style="margin: 0; font-size: 0.92rem; color: #475569; line-height: 1.6;">
                    <span style="font-weight: 700; color: #1e293b; margin-right: 5px;">Response Time:</span> 
                    Our technical team typically responds within <span style="color: #6366f1; font-weight: 600;">24-48 hours</span>. 
                    For urgent account resets, please contact the <span style="color: #6366f1; font-weight: 600;">Development Team</span> directly.
                </p>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <div class="card" style="border-top: 6px solid #6366f1; background: #ffffff;">
            <div class="card-header" style="background: #ffffff; padding: 25px 30px; border-bottom: 1px solid #f1f5f9;">
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #1e293b !important;">Send Us a Message</h3>
                <p style="margin: 3px 0 0; font-size: 0.85rem; color: #64748b !important;">Fill out the form below and we'll get back to you</p>
            </div>
            <div class="card-body" style="padding: 30px;">
                <form method="POST">
                    <?php csrf_field(); ?>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block;">Your Name</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['full_name']) ?>" readonly style="background: #f8fafc;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block;">Your Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($userEmail) ?>" readonly style="background: #f8fafc;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block;">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="What is this regarding?" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 25px;">
                        <label style="font-weight: 700; color: #475569; margin-bottom: 8px; display: block;">Message</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Describe your concern in detail..." required></textarea>
                    </div>
                    
                    <button type="submit" name="submit_contact" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 700; border-radius: 8px;">
                        <i class="fa-solid fa-paper-plane" style="margin-right: 8px;"></i> Send Message
                    </button>
                </form>
            </div>
        </div>

        <div class="card" style="border-top: 6px solid #f59e0b; background: #ffffff;">
            <div class="card-header" style="background: #ffffff; padding: 25px 30px; border-bottom: 1px solid #f1f5f9;">
                <h3 style="margin: 0; font-size: 1.2rem; font-weight: 800; color: #1e293b !important;">Frequently Asked Questions</h3>
                <p style="margin: 3px 0 0; font-size: 0.85rem; color: #64748b !important;">Quick answers to common system inquiries</p>
            </div>
            <div class="card-body" style="padding: 30px;">
                <div class="faq-item">
                    <h4 style="font-weight: 700; color: #1e293b !important; margin-bottom: 10px; font-size: 0.95rem;">I forgot my password. How can I recover it?</h4>
                    <p style="font-size: 0.85rem; color: #64748b !important; line-height: 1.5;">You can use the "Forgot Password" link on the login page to initiate a secure reset via your registered email address.</p>
                </div>

                <div class="faq-item">
                    <h4 style="font-weight: 700; color: #1e293b !important; margin-bottom: 10px; font-size: 0.95rem;">My fingerprint is not registering. What should I do?</h4>
                    <p style="font-size: 0.85rem; color: #64748b !important; line-height: 1.5;">Ensure your finger is clean and dry. If registration persists in failing, please visit the IT office for a hardware recalibration.</p>
                </div>

                <div class="faq-item">
                    <h4 style="font-weight: 700; color: #1e293b !important; margin-bottom: 10px; font-size: 0.95rem;">How can I view my attendance history?</h4>
                    <p style="font-size: 0.85rem; color: #64748b !important; line-height: 1.5;">Navigate to the "Attendance Reports" page from the side menu to view and filter your personal time logs and summaries.</p>
                </div>

                <div style="margin-top: 25px; padding: 20px; background: #fffbeb; border-radius: 12px; border: 1px solid #fde68a; text-align: center;">
                    <p style="margin: 0; font-size: 0.85rem; color: #92400e; font-weight: 600;">Still need help?</p>
                    <p style="margin: 5px 0 0; font-size: 0.8rem; color: #b45309;">Email us at support.biometric@gmail.com</p>
                </div>
            </div>
        </div>

    </div>
</div>

<div id="contactStatusModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; width: 90%; max-width: 450px; border-radius: 16px; border-top: 8px solid <?= !empty($success) ? '#10b981' : '#ef4444' ?>; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 40px 30px; text-align: center;">
            <div style="width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 40px; <?= !empty($success) ? 'background: #ecfdf5; color: #10b981;' : 'background: #fef2f2; color: #ef4444;' ?>">
                <i class="fa-solid <?= !empty($success) ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
            </div>
            <h3 style="font-weight: 800; color: #1e293b; font-size: 1.5rem; margin-bottom: 10px;">
                <?= !empty($success) ? 'Message Sent!' : 'Oops! Error' ?>
            </h3>
            <p style="color: #64748b; line-height: 1.6;">
                <?= (!empty($success)) ? htmlspecialchars($success ?? '') : htmlspecialchars($error ?? '') ?>
            </p>
            <button onclick="document.getElementById('contactStatusModal').style.display='none'" style="margin-top: 30px; background: #1e293b; color: white; border: none; padding: 12px 40px; border-radius: 50px; font-weight: 700; cursor: pointer;">
                Close
            </button>
        </div>
    </div>
</div>

<script>
<?php if (!empty($success) || !empty($error)): ?>
        setTimeout(function() {
            const modal = document.getElementById('contactStatusModal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }, 100);
    <?php endif; ?>
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>