function changeStatus(ele) {
    console.log(ele);
    document.location.href = 'admin.php?page=version_pages&status='+ele.value;
}

function filterByOption(page,option,ele) {
    document.location.href = 'admin.php?page='+page+'&'+option+'='+ele.value;
}