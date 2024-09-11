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

  // Add a new link called "Generate openLRS login" in the body
  // But before adding that link, we need to find if the user is an admin or a teacher, to do that we need to find the role of the user
  // This can be done by calling the is_teacher_or_admin function
  const endpoint = M.cfg.wwwroot + "/webservice/rest/server.php";

  // Call the is_teacher_or_admin function
  $?.ajax({
    url: endpoint,
    type: "POST",
    data: {
      wstoken: token,
      wsfunction: "local_openlrs_is_teacher_or_admin",
      moodlewsrestformat: "json",
      courseid: M?.cfg?.courseId,
    },
    success: function (response) {
      // Check if the user is a teacher or an admin
      if (String(response) === "true") {
        addGenerateLoginLink();
      }
    },
    error: function (xhr, status, error) {
      console.error("Error:", xhr.responseText);
    },
  });

  // Function to add the link
  function addGenerateLoginLink() {
    // Create a div element to hold the generate login link, status label and openLRS login link

    const div = document.createElement("div");
    div.id = "openlrs-login";
    div.style = "display: flex; flex-direction: column;";

    // Create a label to show the status of the login
    const status = document.createElement("label");
    status.innerText = "";
    div.appendChild(status);

    const link = document.createElement("a");
    link.href = "#";
    link.innerText = "Generate openLRS login";
    div.appendChild(link);

    const openLRSLink = document.createElement("a");
    openLRSLink.href = "#";
    openLRSLink.innerText = "";
    div.appendChild(openLRSLink);

    // Find the only iframe in the page
    const iframe = document.querySelector("iframe");

    // Append the div after the iframe
    iframe.parentNode.insertBefore(div, iframe.nextSibling);

    // Add event listener to the link
    link.addEventListener("click", function (event) {
      event.preventDefault();

      link.style.display = "none";
      status.innerText = "Generating login token...";

      // Get the user's ID
      $?.ajax({
        url: endpoint,
        type: "POST",
        data: {
          wstoken: token,
          wsfunction: "local_openlrs_generate_magic_login_token",
          moodlewsrestformat: "json",
          courseid: M?.cfg?.courseId,
        },
        success: function (response) {
          // Response is in string format, convert it to JSON
          response = JSON.parse(response);

          link.style.display = "none";

          status.innerText =
            "Magic token generated successfully: " +
            response?.user?.magicLoginToken;

          openLRSLink.href =
            response?.lrsUrl +
            "login-magic-token/" +
            response?.user?.magicLoginToken;
          openLRSLink.innerText =
            "Click here to login to openLRS automatically";

          // Link to open in a new tab
          openLRSLink.target = "_blank";
        },
        error: function (xhr, status, error) {
          console.error("Error:", xhr.responseText);
          status.innerText = "Error generating login token";
        },
      });
    });
  }
});
