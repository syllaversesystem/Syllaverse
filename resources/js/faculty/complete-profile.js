document.addEventListener('DOMContentLoaded', function () {
	var form = document.getElementById('svCompleteProfileForm');
	var step1 = document.getElementById('svStep1');
	var step2 = document.getElementById('svStep2');
	var nextBtn = document.getElementById('svNextToStep2');
	var backBtn = document.getElementById('svBackToStep1');
	var badge1 = document.getElementById('svStepBadge1');
	var badge2 = document.getElementById('svStepBadge2');
	var step2Label = document.getElementById('svStep2Label');
	var progress = document.getElementById('svStepProgress');

	function validateStep1() {
		if (!form) return true;
		var fields = [
			document.getElementById('svName'),
			document.getElementById('svEmail'),
			document.getElementById('svDesignation'),
			document.getElementById('svEmployeeCode')
		].filter(Boolean);

		var valid = true;
		form.classList.add('was-validated');
		for (var i = 0; i < fields.length; i++) {
			var el = fields[i];
			if (!el.checkValidity()) {
				valid = false;
			}
		}
		if (!valid) {
			var firstInvalid = fields.find(function (el) { return !el.checkValidity(); });
			if (firstInvalid) {
				try { firstInvalid.focus({ preventScroll: true }); } catch (e) {}
				try { firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
				try { firstInvalid.reportValidity(); } catch (e) {}
			}
		}
		return valid;
	}

	function goToStep2() {
		if (!step1 || !step2) return;
		step1.hidden = true;
		step2.hidden = false;
		if (badge1) badge1.classList.remove('sv-step-active');
		if (badge2) {
			badge2.classList.remove('sv-step-disabled');
			badge2.classList.add('sv-step-active');
		}
		if (step2Label) {
			step2Label.classList.remove('text-muted');
			step2Label.classList.add('text-dark');
		}
		if (progress) progress.style.width = '100%';
	}

	function goToStep1() {
		if (!step1 || !step2) return;
		step1.hidden = false;
		step2.hidden = true;
		if (badge1) badge1.classList.add('sv-step-active');
		if (badge2) {
			badge2.classList.add('sv-step-disabled');
			badge2.classList.remove('sv-step-active');
		}
		if (step2Label) {
			step2Label.classList.add('text-muted');
			step2Label.classList.remove('text-dark');
		}
		if (progress) progress.style.width = '50%';
	}

	if (nextBtn) {
		nextBtn.addEventListener('click', function (e) {
			if (nextBtn.disabled) return;
			if (!validateStep1()) return;
			goToStep2();
		});
	}

	if (backBtn) {
		backBtn.addEventListener('click', function () {
			goToStep1();
		});
	}

	if (step1) {
		step1.addEventListener('keydown', function (e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				if (nextBtn && !nextBtn.disabled) {
					if (!validateStep1()) return;
					goToStep2();
				}
			}
		});
	}

	var leadershipIds = ['request_dept_head', 'request_dean', 'request_assoc_dean'];
	var facultyId = 'request_faculty';

	function anyLeadershipSelected() {
		for (var i = 0; i < leadershipIds.length; i++) {
			var cb = document.getElementById(leadershipIds[i]);
			if (cb && cb.checked) return true;
		}
		return false;
	}

	function updateFacultyUI() {
		var facultyCb = document.getElementById(facultyId);
		var sec = document.getElementById('svFacultyDepartmentSelector');
		var sel = document.getElementById('svFacultyDepartmentId');
		var show = !!(facultyCb && facultyCb.checked && !facultyCb.disabled);
		if (sec) sec.style.display = show ? '' : 'none';
		if (sel) sel.required = show && !sel.disabled;
	}

	function updateLeadershipUI() {
		var any = anyLeadershipSelected();
		var depSec = document.getElementById('svDepartmentSelector');
		var depSel = document.getElementById('svDepartmentId');
		if (depSec) depSec.style.display = any ? '' : 'none';
		if (depSel) depSel.required = any && !depSel.disabled;

		var facultyCb = document.getElementById(facultyId);
		if (any) {
			if (facultyCb) {
				facultyCb.checked = false;
				facultyCb.disabled = true;
			}
			var fSec = document.getElementById('svFacultyDepartmentSelector');
			var fSel = document.getElementById('svFacultyDepartmentId');
			if (fSec) fSec.style.display = 'none';
			if (fSel) fSel.required = false;
		} else {
			if (facultyCb) facultyCb.disabled = false;
			updateFacultyUI();
		}

		// Mutual restriction: Department Head (request_dean) <-> Associate Dean (request_assoc_dean)
		var deptHeadRoleCb = document.getElementById('request_dean');
		var assocDeanCb = document.getElementById('request_assoc_dean');
		if (deptHeadRoleCb && assocDeanCb) {
			if (!assocDeanCb.hasAttribute('data-hard-disabled')) {
				assocDeanCb.disabled = !!deptHeadRoleCb.checked || assocDeanCb.hasAttribute('data-hard-disabled');
				if (deptHeadRoleCb.checked) assocDeanCb.checked = false;
			}
			if (!deptHeadRoleCb.hasAttribute('data-hard-disabled')) {
				deptHeadRoleCb.disabled = !!assocDeanCb.checked || deptHeadRoleCb.hasAttribute('data-hard-disabled');
				if (assocDeanCb.checked) deptHeadRoleCb.checked = false;
			}
		}
	}

	leadershipIds.forEach(function (id) {
		var cb = document.getElementById(id);
		if (cb) cb.addEventListener('change', updateLeadershipUI);
	});

	var facultyCbInit = document.getElementById(facultyId);
	if (facultyCbInit) facultyCbInit.addEventListener('change', updateFacultyUI);

	updateLeadershipUI();
	updateFacultyUI();
});
