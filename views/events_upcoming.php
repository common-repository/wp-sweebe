
<div class="sweebedotcom_upcoming_events instance-<?php print $instance; ?>">
    <?php
    foreach ($events as $event):
        $ts_date_from_local = strtotime($event['main']['date_from_local']);
        static $i = -1;
        $i++;
        ?>
        <div class="event <?php print (($i % 2) == 0) ? 'even' : 'odd'; ?>">
            <h2><a href="<?php print $event['path']['url']; ?>"><?php print $event['main']['name']; ?></a> at <a href="<?php print $event['venue']['path']['url']; ?>"><?php print $event['venue']['main']['name']; ?></a></h2>
            <p class="date"><?php print date('l', $ts_date_from_local); ?> at <?php print date('h:ia', $ts_date_from_local); ?> - <?php print date('F jS Y', $ts_date_from_local); ?></p>
            <div class="content">
                <div class="image">
                    <a href="<?php print $event['path']['url']; ?>"><img src="http://www.sweebe.com/cdn/image/<?php print $event['main']['image_cover_id']; ?>?present=mini_crop" /></a>
                </div>
                <div class="excerpt">
                    <?php print $event['main']['description']; ?> 
                    <div class="read-more"><a href="<?php print $event['path']['url']; ?>">Read More</a></div>
                </div>
                <div class="fixed">&nbsp;</div>
            </div>
        </div>

    <?php endforeach; ?>

    <div class="show-all">
        <a href="http://www.sweebe.com/ie/dublin/events?source=<?php print $current_url; ?>">Show all upcoming Events</a>
    </div>
    <span class="fixed" />
</div>