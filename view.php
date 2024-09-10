<?php
require_once('../../config.php');
require_login();

require_capability('local/openlrs:view', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/openlrs/view.php'));
$PAGE->set_title('OpenLRS');
$PAGE->set_heading('OpenLRS External Content');

echo $OUTPUT->header();

// Output JavaScript to capture courseId and make a fetch call
?>
<iframe id="lrs-iframe" style="display:none; width: 100%; height: 800px; border: none;"></iframe>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const courseId = window.M?.cfg?.courseId;
        if (courseId) {
            // Make a fetch call to the server with the courseId
            fetch('<?php echo $CFG->wwwroot ?>/local/openlrs/create_temp_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'courseId=' + encodeURIComponent(courseId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const iframe = document.getElementById('lrs-iframe');
                    iframe.src = data.lrsUrl + "login-magic-token/" + encodeURIComponent(data.user.magicLoginToken);
                    iframe.style.display = 'block';
                } else {
                    console.error('Failed to create temporary user:', data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        } else {
            console.error('Could not retrieve courseId');
        }
    });
</script>
<?php

echo $OUTPUT->footer();
