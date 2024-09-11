# openLRS Plugin for Moodle

The openLRS Plugin for Moodle provides real-time analytics and reporting capabilities by capturing xAPI events from H5P content. This plugin allows Moodle administrators to send xAPI data to an external openLRS for further analysis. It also features magic links for logging admins & teachers into openLRS.

## Features

- Captures xAPI events from H5P content within Moodle.
- Configurable settings for connecting to an openLRS system.
- Secure communication through a secret key.
- Magic links for logging admins & teachers into openLRS.

## Installation

### Step 1: Install the Plugin

1. Download the openLRS plugin repository as a ZIP file.
2. Log in to your Moodle site as an admin and go to `Site administration > Plugins > Install plugins`.
3. Upload the ZIP file and follow the on-screen instructions to install the plugin.

### Step 2: Configure the Plugin

After installation, you need to configure the plugin with your external LRS settings.

1. Navigate to `Site administration > Plugins > Local plugins > openLRS settings`.
2. Enter the **External Website Path**, which is the URL of your external LRS.
3. Enter a **Secret Key** and **Consumer ID** for secure communication with your LRS.
4. Save the changes.

### Step 3: Generate a Web Service Token

1. Go to `Site administration > Server > Web services > Manage tokens`.
2. Create a new token for the user account that will be sending data to the LRS.
3. Note down the generated token.

### Step 4: Configure the H5P Integration

1. Open the `js/h5p.js` file from the plugin directory.
2. Replace the `var token = "your generated token here";` line with the token generated in the previous step.
3. Log in to your Moodle site as an admin and navigate to `Site administration > Appearance > Additional HTML`.
4. In the `Within HEAD` section, add the modified `h5p.js` script inside `<script>` tags.

```html
<script>
  // Your modified h5p.js content goes here
</script>
```
