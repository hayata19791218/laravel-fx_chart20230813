const valueDelete = document.getElementById("delete");
const valueRow = document.getElementById("row");

valueDelete.addEventListener('click', () => {
    if(confirm("情報を消していいですか？")) valueRow.remove();
    
})