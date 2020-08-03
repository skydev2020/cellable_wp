function changeStatus(ele) {
    console.log(ele);
    // var status = $(this).val();
    // if( catFilter != '' ){
        document.location.href = 'admin.php?page=version_pages&status='+ele.value;    
    // }
}
