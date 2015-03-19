<form>
    <input type="hidden" id="data_request_rate" value="<?php echo $requestRate; ?>"/>
    <input type="hidden" id="data_max_request_rate" value="<?php echo $maxRequestRate; ?>"/>
</form>

<ul id="new-requests" style="display: none; list-style-type: none">
    <li data-id="requests_per_sec" onclick="window.location = 'processes';">
        <div class="list-item running-item title-item">Requests Per Second<span class='title-value'><?php echo $requestRate; ?></span>
        </div>
        <br/>
    </li>
<?php foreach($meters as $meter): ?>
    <li data-id="<?php echo $meter['meter_id'] ?>">
        <div class="list-item running-item <?php echo $meter['class']; ?>">
            <div class="meter-id"><?php echo $meter['meter_id']; ?></div>
            <span class='meter-rate'><?php echo number_format($meter['tokens']) ?></span>
        </div>
    </li>
<?php endforeach ?>
</ul>
