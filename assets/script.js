function openModal() {
    document.getElementById("addModal").style.display = "block";
  }

  function closeModal() {
    document.getElementById("addModal").style.display = "none";
  }

  // Закрытие при клике вне окна
  window.onclick = function(event) {
    const modal = document.getElementById("addModal");
    if (event.target === modal) {
      closeModal();
    }
  }


//   удаление товара
function deleteProduct(id) {
    if (confirm("Удалить товар?")) {
      fetch(`src/delete_product.php?id=${id}`, {
        method: "GET",
      })
      .then(res => {
        if (res.ok) location.reload();
        else throw new Error("Ошибка удаления");
      })
      .catch(err => alert("Ошибка: " + err));
    }
  }

//   редактирование
function openEditModal(id, name, quantity, price) {
    document.getElementById("editId").value = id;
    document.getElementById("editName").value = name;
    document.getElementById("editQuantity").value = quantity;
    document.getElementById("editPrice").value = price;
    document.getElementById("editModal").style.display = "block";
  }
  
  function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
  }
  
  document.getElementById("editProductForm").addEventListener("submit", function(e) {
    e.preventDefault();
  
    const formData = new FormData(this);
    fetch("src/edit_product.php", {
      method: "POST",
      body: formData
    })
    .then(res => {
      if (res.ok) {
        closeEditModal();
        location.reload();
      } else {
        throw new Error("Ошибка редактирования");
      }
    })
    .catch(err => alert("Ошибка: " + err));
  });
  