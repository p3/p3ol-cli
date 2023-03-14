# 🖥 P3OL CLI
[![GitHub Tests Action Status](https://github.com/p3/p3ol-cli/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/p3/p3ol-cli/actions/workflows/run-tests.yml)
<span class="badge-patreon"><a href="https://www.patreon.com/project_p3ol" title="Donate to the P3OL project using Patreon"><img src="https://img.shields.io/badge/patreon-donate-green.svg" alt="Patreon donate button" /></a></span>

This is a MacOS/Linux CLI client built in Laravel for the [P3OL project](https://www.patreon.com/project_p3ol) (an America Oline compatiable server written in Python).

**Table of Contents**

- [Installation & Usage](#installation--usage)
- [Self Updates](#self-updates)
- [Features](#features)
    - [Chat Commands](#chat-commands)
- [Feature Pipeline](#feature-pipeline)

![image](https://user-images.githubusercontent.com/3619398/173594316-ea31862b-741a-4f20-872d-d8e2a0c82bc7.png)

## Installation & Usage

> ### Requirements
> You will need PHP 8.1 installed on your system.

```bash
# Install P3OL-ClI
curl -L https://github.com/p3/p3ol-cli/raw/main/install.sh | sh

# Launch
./p3ol
```

## Self Updates

You can self-update your client by running `./p3ol self-update`.

## Features

Here is a running list of features and future features I plan to implem

- Logging in as a Guest account
- Joining a public chatroom
- System notifications when your screenname 

![image](https://user-images.githubusercontent.com/3619398/225140733-acd10f33-895c-4dc1-a722-0d269e529fa5.png)

### Chat Commands

- `/quit` – Quits the chat and closes Retro AOL CLI
- `/here` – Display a table of all users currently joined to chat
- `/im` `{$screenName}` `{$message}` Send an instant message.
- `/idle {$reason}` Start a chat idler with your specified reason
- `/idleoff` Stop your chat idler
- `/handle` Set your handle (you will receive notifications when your handle is mentioned)
- `/uptime` Display in chat how long you've been signed on
- `/profile` `{$screenName}` View member directory pro

#### Feature Pipeline
- [x] Screen name authentication
- [ ] Creating public/private chatrooms
- [x] Sending/receiving instant messages (IMs)


## Support

The P3OL server project is funded by donations. Please consider donating here:

<span class="badge-patreon"><a href="https://www.patreon.com/project_p3ol" title="Donate to this project using Patreon"><img src="https://img.shields.io/badge/patreon-donate-green.svg" alt="Patreon donate button" /></a></span>

✨ Gold Tier Member Perks:

★ Early Access to P3OL (Screen Name Registration)
Try out P3OL as an account holder before the rest of the world. Receive monthly updates via Discord as the project grows further in development. Your feedback will be taken into consideration during the development phase.

★ "Gold Supporter" role on Discord server
Join the Discord server and get the "Gold Supporter" role to show your awesome support.
