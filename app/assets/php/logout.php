<?php
session_start();
session_unset();
session_destroy();
?>
<script>
sessionStorage.clear();
</script>
<?php
header("Location: ../../auth/sign-in.html");
?>