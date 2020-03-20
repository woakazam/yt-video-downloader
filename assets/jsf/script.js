$(() => {

	$(document).on("click", "#btn-ajax", (e) => {
		let el = $(e.currentTarget);
		$(el).prop("disabled", true);
		$.ajax({
			type: "POST",
			url: "/app/ajax/get.php",
			dataType: "json",
			data: { "id": $("#videoID").val() }
		}).done((rt) => {
			if (rt.a === 500) {
				Swal.fire({
					title: rt.b,
					text: rt.c,
					type: rt.d,
					allowEscapeKey: false,
					allowOutsideClick: false
				}).then((result) => {
					if (result.value) {
						location.reload();
					}
				});
			} else {
				$(el).prop("disabled", false);
				$("#downloadList tbody").empty();
				$(rt.b).each((a, b) => {
					let newLine = "";
					newLine += "<tr>";
					newLine += "<th>" + Number(a + 1) + "</th>";
					newLine += "<td>" + b.Code + "</td>";
					newLine += "<td>" + b.Rate + "</td>";
					newLine += "<td>" + b.Type + "</td>";
					newLine += "<td>" + b.Format + "</td>";
					newLine += "<td><a class='nav-link' href='" + b.Link + "' target='_blank'>" + "<i class='fas fa-angle-double-down fa-fw'></i>" + "</td>";
					newLine += "</tr>";
					$("#downloadList tbody").append(newLine);
				});
			}
		});
	})

});