var token = "your generated token here";
// Function to check for window.H5P and catch xAPI events
function catchH5PEvents() {
  if (typeof window.H5P !== "undefined" && window.H5P.externalDispatcher) {
    // Listen for xAPI events
    window.H5P.externalDispatcher.on("xAPI", function (event) {
      // Send xAPI statements to Trax Logs plugin (replace with your implementation)
      const endpoint = M.cfg.wwwroot + "/webservice/rest/server.php";

      let dataToSend = {};
      dataToSend.xAPI = event.data.statement;

      dataToSend.metadata = {};
      dataToSend.metadata.session = {};

      // Get Moodle's Course ID
      dataToSend.metadata.session.context_id = M?.cfg?.courseId;

      $?.ajax({
        url: endpoint,
        type: "POST",
        data: {
          wstoken: token,
          wsfunction: "local_openlrs_handle_data",
          moodlewsrestformat: "json",
          data: JSON.stringify(dataToSend),
        },
        success: function (response) {},
        error: function (xhr, status, error) {
          console.error("Error:", xhr.responseText);
        },
      });
    });
  } else {
    // If H5P is not yet injected, use MutationObserver to detect changes
    const observer = new MutationObserver(() => {
      if (typeof window.H5P !== "undefined" && window.H5P.externalDispatcher) {
        observer.disconnect(); // Stop observing after H5P is found
        catchH5PEvents(); // Call the function to catch events
      }
    });

    observer.observe(document.body, { childList: true, subtree: true });
  }
}

// Add event listener to load the script when the page is fully loaded
window.addEventListener("load", function (event) {
  catchH5PEvents();
});
