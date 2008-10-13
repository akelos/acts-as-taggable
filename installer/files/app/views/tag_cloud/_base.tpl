<link href="{tc_stylesheet}" rel="stylesheet"/>
<div id="tag_cloud">
<ul class="cloud">
{loop tc_tags}
<li class="{tc_tag-class}">{?tc_tag-link}<a href="{tc_tag-link}">{end}{tc_tag-tag}{?tc_tag-link}</a>{end}</li>
{end}
</ul>
</div>