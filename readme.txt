=== SponsorMe ===
Contributors: Owen Cutajar
Donate link: http://www.u-g-h.com
Tags: sidebar, sponsor, money, donations, charity, fundraising, graph, paypal, funds
Requires at least: 2.2
Tested up to: 2.5.1
Stable tag: /trunk/

Plugin to run a sponsorship campaign that lets friends and family contribute to a target amount.

== Description ==

The SponsorMe plugin lets you organise your fund raising on your WordPress blog. It shows a graph of your target and how much money you have collected. It also lets users pledge an amount to you and sends them to PayPal to collect money directly from them.
Most information at: http://www.wpauctions.com/

== Installation ==

1. Download the plugin file, unzip and place it in your plugin folder.

2. Activate the plugin

3a. Add this snippet to your sidebar: `<?php SponsorMe_sidebar(); ?>`
3b. Alternatively, the plugin is also widget-aware and will appear in your widget list.

4. Create a new page with some information about your sponsorship campaign. Add this tag to your page: <!--SponsorMe-page-->

5. Configure the plugin from the SponsorMe menu in your Administration Section. Specify the currency you want to sell in, your Paypal address, your target amount and the Page ID of the page you created in step 4.

6. Wait for pledges to come in!

When a user pledges an amount, they will be taken straigh to PayPal to donate money. Once you receive the PayPal email to confirm the amount has been received, log into the plugin page and click "Verify" next to the payment you have received. This updates the graph and moves the payment to the confirmed list.

== Frequently Asked Questions ==

= Where can I report any problems? =

Head down to http://www.u-g-h.com/index.php/wordpress-plugins/wordpress-plugin-sponsorme/ and leave me a comment. I'll try and help as best I can.

== Screenshots ==

1. SponsorMe graph in sidebar
2. Sponsorship Page
3. Administration interface