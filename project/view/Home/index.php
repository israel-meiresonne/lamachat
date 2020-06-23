<?php
$this->title = "Discussions";
?>
<!-- Side Navigation -->
<nav class="w3-sidebar w3-bar-block w3-collapse w3-white w3-animate-left w3-card" style="z-index:3;width:320px;" id="mySidebar">
    <a href="javascript:void(0)" class="w3-bar-item w3-button w3-border-bottom w3-large"><img src="https://www.w3schools.com/images/w3schools.png" style="width:60%;"></a>
    <a href="javascript:void(0)" onclick="w3_close()" title="Close Sidemenu" class="w3-bar-item w3-button w3-hide-large w3-large">Close <i class="fa fa-remove"></i></a>
    <a href="javascript:void(0)" class="w3-bar-item w3-button w3-dark-grey w3-button w3-hover-black w3-left-align" onclick="document.getElementById('id01').style.display='block'">New Message <i class="w3-padding fa fa-pencil"></i></a>
    <a id="myBtn" onclick="myFunc('Demo1')" href="javascript:void(0)" class="w3-bar-item w3-button"><i class="fa fa-inbox w3-margin-right"></i>Inbox (3)<i class="fa fa-caret-down w3-margin-left"></i></a>
    <div id="Demo1" class="w3-hide w3-animate-left">
        <a href="javascript:void(0)" class="w3-bar-item w3-button w3-border-bottom test w3-hover-light-grey" onclick="openMail('Borge');w3_close();" id="firstTab">
            <div class="w3-container">
                <img class="w3-round w3-margin-right" src="content/images/static/default-user-picture.png" style="width:15%;"><span class="w3-opacity w3-large">Borge Refsnes</span>
                <h6>Subject: Remember Me</h6>
                <p>Hello, i just wanted to let you know that i'll be home at...</p>
            </div>
        </a>
        <a href="javascript:void(0)" class="w3-bar-item w3-button w3-border-bottom test w3-hover-light-grey" onclick="openMail('Jane');w3_close();">
            <div class="w3-container">
                <img class="w3-round w3-margin-right" src="content/images/static/default-user-picture.png" style="width:15%;"><span class="w3-opacity w3-large">Jane Doe</span>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit...</p>
            </div>
        </a>
        <a href="javascript:void(0)" class="w3-bar-item w3-button w3-border-bottom test w3-hover-light-grey" onclick="openMail('John');w3_close();">
            <div class="w3-container">
                <img class="w3-round w3-margin-right" src="content/images/static/default-user-picture.png" style="width:15%;"><span class="w3-opacity w3-large">John Doe</span>
                <p>Welcome!</p>
            </div>
        </a>
    </div>
    <a href="#" class="w3-bar-item w3-button"><i class="fa fa-paper-plane w3-margin-right"></i>Sent</a>
    <a href="#" class="w3-bar-item w3-button"><i class="fa fa-hourglass-end w3-margin-right"></i>Drafts</a>
    <a href="#" class="w3-bar-item w3-button"><i class="fa fa-trash w3-margin-right"></i>Trash</a>
</nav>

<!-- Modal that pops up when you click on "New Message" -->
<div id="id01" class="w3-modal" style="z-index:4">
    <div class="w3-modal-content w3-animate-zoom">
        <div class="w3-container w3-padding w3-red">
            <span onclick="document.getElementById('id01').style.display='none'" class="w3-button w3-red w3-right w3-xxlarge"><i class="fa fa-remove"></i></span>
            <h2>Send Mail</h2>
        </div>
        <div class="w3-panel">
            <label>To</label>
            <input class="w3-input w3-border w3-margin-bottom" type="text">
            <label>From</label>
            <input class="w3-input w3-border w3-margin-bottom" type="text">
            <label>Subject</label>
            <input class="w3-input w3-border w3-margin-bottom" type="text">
            <input class="w3-input w3-border w3-margin-bottom" style="height:150px" placeholder="What's on your mind?">
            <div class="w3-section">
                <a class="w3-button w3-red" onclick="document.getElementById('id01').style.display='none'">Cancel  <i class="fa fa-remove"></i></a>
                <a class="w3-button w3-light-grey w3-right" onclick="document.getElementById('id01').style.display='none'">Send  <i class="fa fa-paper-plane"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Overlay effect when opening the side navigation on small screens -->
<div class="w3-overlay w3-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="Close Sidemenu" id="myOverlay"></div>

<!-- Page content -->
<div class="w3-main" style="margin-left:320px;">
    <i class="fa fa-bars w3-button w3-white w3-hide-large w3-xlarge w3-margin-left w3-margin-top" onclick="w3_open()"></i>
    <a href="javascript:void(0)" class="w3-hide-large w3-red w3-button w3-right w3-margin-top w3-margin-right" onclick="document.getElementById('id01').style.display='block'"><i class="fa fa-pencil"></i></a>

    <div id="Borge" class="msg-window w3-container person">
        <div class="msg-background"></div>
        <div class="msg-window-inner">
            <div class="msg-wrap">
                <div class="msg-date">
                    <span>mercredi 23 juin 2020</span>
                </div>
            </div>
            <div class="msg-wrap">
                <div class="msg-text msg-left">
                    <span>🤬Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec🥶</span>
                    <div class="msg-info">
                        <div class="msg-info-inner">
                            <span class="msg-time">19:17</span>
                            <div class="msg-status">
                                <div class="v_symbol-wrap">
                                    <span class="v_symbol-vertical"></span>
                                    <span class="v_symbol-horizontal"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="msg-wrap">
                <div class="msg-wrap-inner">
                    <div class="msg-text msg-right">
                        <span>Ut velit mauris, egestas sed, gravida nec, ornare ut, mi.🤬</span>
                        <div class="msg-info">
                            <div class="msg-info-inner">
                                <span class="msg-time">19:17</span>
                                <div class="msg-status">
                                    <div class="o_symbol-wrap">
                                        <div class="o_symbol"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="msg-wrap">
                <div class="msg-wrap-inner">
                    <div class="msg-text msg-right">
                        <span>🤬Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec🥶</span>
                        <div class="msg-info">
                            <div class="msg-info-inner">
                                <span class="msg-time">19:17</span>
                                <div class="msg-status">
                                    <div class="v_symbol-wrap">
                                        <span class="v_symbol-vertical"></span>
                                        <span class="v_symbol-horizontal"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="msg_sender-container">
            <div class="msg_sender">
            <div class="msg_sender-inner">
                <div class="msg_sender-placeholder">Entrer votre message</div>
                <div class="msg_sender-input" contenteditable="true"></div>
            </div>
            </div>
            <div class="msg_sender-button-set">
                <div class="msg_sender-button-div">
                    <button id="send_txt_msg" class="img-button remove-button-default-att">
                        <img src="content/images/static/icons8-email-send-96.png">
                    </button>
                </div>
                <div class="msg_sender-button-div">
                    <button id="send_img_msg" class="img-button remove-button-default-att">
                        <img src="content/images/static/icons8-image-100.png">
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="Jane" class="discusion-container w3-container person">
        <br>
        <img class="w3-round w3-animate-top" src="content/images/static/default-user-picture.png" style="width:20%;">
        <h5 class="w3-opacity">Subject: None</h5>
        <h4><i class="fa fa-clock-o"></i> From Jane Doe, Sep 25, 2015.</h4>
        <a class="w3-button w3-light-grey">Reply<i class="w3-margin-left fa fa-mail-reply"></i></a>
        <a class="w3-button w3-light-grey">Forward<i class="w3-margin-left fa fa-arrow-right"></i></a>
        <hr>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
        <p>Forever yours,<br>Jane</p>
    </div>

    <div id="John" class="discusion-container w3-container person">
        <br>
        <img class="w3-round w3-animate-top" src="content/images/static/default-user-picture.png" style="width:20%;">
        <h5 class="w3-opacity">Subject: None</h5>
        <h4><i class="fa fa-clock-o"></i> From John Doe, Sep 23, 2015.</h4>
        <a class="w3-button w3-light-grey">Reply<i class="w3-margin-left fa fa-mail-reply"></i></a>
        <a class="w3-button w3-light-grey">Forward<i class="w3-margin-left fa fa-arrow-right"></i></a>
        <hr>
        <p>Welcome.</p>
        <p>That's it!</p>
    </div>

</div>

<script>
    // var openInbox = document.getElementById("myBtn");
    // openInbox.click();

    // function w3_open() {
    //     document.getElementById("mySidebar").style.display = "block";
    //     document.getElementById("myOverlay").style.display = "block";
    // }

    // function w3_close() {
    //     document.getElementById("mySidebar").style.display = "none";
    //     document.getElementById("myOverlay").style.display = "none";
    // }

    // function myFunc(id) {
    //     var x = document.getElementById(id);
    //     if (x.className.indexOf("w3-show") == -1) {
    //         x.className += " w3-show";
    //         x.previousElementSibling.className += " w3-red";
    //     } else {
    //         x.className = x.className.replace(" w3-show", "");
    //         x.previousElementSibling.className =
    //             x.previousElementSibling.className.replace(" w3-red", "");
    //     }
    // }

    // openMail("Borge")

    // discusion-container function openMail(personName) {
    //     var i;
    //     var x = documentdiscusion-container .getElementsByClassName("person");
    //     for (i = 0; i < x.length; i++) {
    //         x[i].style.display = "none";
    //     }
    //     x = document.getElementsByClassName("test");
    //     for (i = 0; i < x.length; i++) {
    //         x[i].className = x[i].className.replace(" w3-light-grey", "");
    //     }
    //     documentdiscusion-container .getElementById(personName).style.display = "block";
    //     event.currentTarget.className += " w3-light-grey";
    // }
</script>

<script>
    // var openTab = document.getElementById("firstTab");
    // openTab.click();
</script>