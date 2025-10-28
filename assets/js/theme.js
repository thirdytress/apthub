// Global SweetAlert2 theming and helpers
(function(){
  if (typeof window === 'undefined') return;
  if (!window.Swal) return; // Only run if SweetAlert2 is present on the page

  // Mixin with unified button styles and animations
  const Unified = Swal.mixin({
    customClass: {
      confirmButton: 'btn btn-primary',
      cancelButton: 'btn btn-outline-secondary'
    },
    buttonsStyling: false,
    showClass: { popup: 'swal2-show animate__animated animate__fadeInDown' },
    hideClass: { popup: 'swal2-hide animate__animated animate__fadeOutUp' }
  });

  // Expose helper APIs
  window.AHAlert = {
    info:  (title, text) => Unified.fire({ icon: 'info',  title, text }),
    error: (title, text) => Unified.fire({ icon: 'error', title, text }),
    success:(title, text) => Unified.fire({ icon: 'success', title, text }),
    confirm:(title, text) => Unified.fire({ icon: 'question', title, text, showCancelButton: true })
  };
})();
