/* --------------------------------------------------------------
	Functions
-------------------------------------------------------------- */
/*
 * Lightweight wrapper for console.log
 * Call console.log by using: log(...)
 */
window.log = function() {

	if (!production) {
		log.history = log.history || [];
		log.history.push(arguments);

		if (this.console) {
			console.log(Array.prototype.slice.call(arguments));
		}
	}

};


// Returns a function, that, as long as it continues to be invoked, will not
// be triggered. The function will be called after it stops being called for
// N milliseconds. If `immediate` is passed, trigger the function on the
// leading edge, instead of the trailing.
function debounce(func, wait, immediate) {

	var timeout;

	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;

			if (!immediate) func.apply(context, args);
		};

		var callNow = immediate && !timeout;

		clearTimeout(timeout);

		timeout = setTimeout(later, wait);

		if (callNow) func.apply(context, args);
	};

}// END debounce


/*
 * Resize variables
 */
function resizeVars() {

	winHeight = win.height();
	winWidth = win.width();

	storyMax = getStoryMax();

}// END resizeVars


/*
 * Resize
 */
function resize() {

	resizeVars();
	setProgressWidth();
	setRelatedSlider();
	showStoryActions();
	storyActionPosition();

    // Hide mobile nav on resize
    if (winWidth >= 768 && body.hasClass('nav-open')) {
	    $('html, body').removeClass('nav-open');
	}

}// END resize


/*
 * Scroll handler
 */
function scrollHandler() {

    storyStickyHdr();

}// END scrollHandler


/*
 * Scroll to top
 *
 * @param position [int] - the int position the window should scroll to
 */
function scrollTo(position) {

	$('html, body').animate({
		scrollTop: position
	}, 250);

}// END scrollToTop


/*
 * Get window scroll position
 */
function getWinScrollTop() {

    return win.scrollTop();

}// END getWinScrollTop


/*
 * Get story scroll position
 */
function getStoryScrollTop() {

    return win.scrollTop() - story.offset().top;

}// END getStoryScrollTop


/*
 * Go back in users browser history
 */
function goBack() {

    window.history.back();

}// END goBack


/**
 * Get URL parameter
 *
 * @param sParam [string] - the param to get the value of
 */
function getUrlParameter(sParam) {

    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }

}// END getUrlParameter


/**
 * Tracking
 *
 * @param item [string] - GA required - Typically the object that was interacted with (e.g. 'Video')
 * @param event [string] - GA required - The type of interaction (e.g. 'play')
 * @param label [string] - GA optional - Useful for categorising events (e.g. 'Fall Campaign')
 * @param url [string] - The URL
 * @param newtab [boolean] - Does the URL need to open in a new tab
 */
function eventTracking(item, event, label, url, newtab) {

	// Reference the window.open outside the GA stuff - Safari doesn't call this function otherwise.
	if (isSafari && newtab)
		var windowReference = window.open();

    var callback = function () {
        if (typeof(url) != 'undefined') {
            if (newtab) {
                // Added Safari conditional check to pass in Safari workaround for opening tabs
                if (isSafari) {
                    // Reference and pass params to the window.open
                    windowReference.location = url;
                    windowReference.name = '_blank';
                }
                else {
                    window.open(url, '_blank');
                }
            }
            else {
                document.location = url;
            }
        }
   };

	gtag(
        'event',
        item,
        {
    		'event': event,
    		'label': label,
            'destination': url,
            'event_callback': callback
    	}
    );

}// END eventTracking


/**
 * Track link
 *
 * @param el [string] - An <a> tag or an element with data-url=""
 * @param item [string] - required - Typically the object that was interacted with (e.g. 'Video')
 * @param event [string] - required - The type of interaction (e.g. 'play')
 * @param label [string] - Useful for categorising events (e.g. 'Fall Campaign')
 */
function trackLink(el, item, event, label) {

	var url,
		target = false,
		labelData;

	// Test to see if element has href attribute
	if (!el.attr('href') && !el.attr('data-href')) {
		console.log('The element does not contain a href or data-href attribute.');
		console.log('Element: ', el);
	}

	// Get href value
	if (el.attr('href')){
		url = el.attr('href');
	}
	else if (el.attr('data-href')) {
		url = el.attr('data-href');
	}

	// Get target value - if there is one
	if (el.attr('target') == '_blank' || el.attr('data-target') == '_blank')
		target = true;

	// Set label data - If label is not set use the href or data-href value
	if (label)
		labelData = label;
	else if (el.attr('href'))
		labelData = el.attr('href').toLowerCase();
	else if (el.attr('data-href'))
		labelData = el.attr('data-href').toLowerCase();

	// console.log('item = ', item.toLowerCase());
	// console.log('event = ', event);
	// console.log('labelData = ', labelData);

	// Track URL
	eventTracking(item.toLowerCase(), event.toLowerCase(), labelData, url, target);

}// END trackLink


/*
 * Validate email address
 *
 * @param email [string] - The email address to validate
 * @return [boolean] - If the email passed the regex test
 */
function validateEmail(email) {

    var regex = /\S+@\S+\.\S+/;

    return regex.test(email);

}// END validateEmail


/*
 * Show error message
 *
 * @param element [string] - The element to append the error message to
 * @param text [string] - The text to show in the error message
 */
function showErrorMessage(element, text) {

    element.append('<div class="form-element-error-message">' + text + '</div>');

}// END showErrorMessage


/*
 * Get story max height/end point
 */
function getStoryMax() {

    return story.outerHeight() - win.height();

}// END getStoryMax


/*
 * Calculate the percentage of the story based on how far the user scrolled down
 */
function getWidth() {

    // Calculate width in percentage
    winScrollTop = getStoryScrollTop();

    progressWidth = (winScrollTop/storyMax) * 100;
    progressWidth = progressWidth > 100 ? 100 + '%': progressWidth + '%';

    return progressWidth;

}// END getWidth


/*
 * Set progress bar width
 */
function setProgressWidth() {

	if (progressBar.length) {
	    progressBar.css({
	        width: getWidth()
	    });
	}

}// END setProgressWidth


/*
 * Sticky story header
 */
function storyStickyHdr() {

	if (story.length) {
	    if (getWinScrollTop() >= mnHdr.outerHeight()) {
	        if (!storyHdr.hasClass('fixed')) {
	            storyHdr.addClass('fixed');

	            // Add margin to main header
				mnHdr.css({
					marginBottom: storyHdrHeight
				});
	        }
	    }
	    else {
	        if (storyHdr.hasClass('fixed')) {
	            storyHdr.removeClass('fixed');

	            // Remove margin from main header
				mnHdr.css({
					marginBottom: 0
				});
	        }
	    }
	}

}// END storyStickyHdr


/**
 * Search box
 * @param  {[string]} toggle Whether to open or close the search box open|close
 */
function searchBox(toggle) {

    if (toggle == 'open') {
        searchBoxShow = true;

        body.addClass('no-scroll');

        $('.search-box').show();
        $('.search-box input').focus();
    }
    else {
        searchBoxShow = false;

        $('.search-box').hide();
        $('.search-box input').val('');

        body.removeClass('no-scroll');
    }

}// END searchBox


/*
 * Story action scroll - don't go past bottom of story
 */
function storyActionsScroll() {

	if (story.length) {
		var storyActionsStuckClass = 'story-actions--stuck';

		// Test if browser size
		if (winWidth >= config.storyActionPositionChange) {

			// Variables for the calculation of when the story actions hits the bottom of the story
			var storyOffsetBottom = story.offset().top + story.outerHeight();
				storyActionMargin = storyActions.outerHeight(true)*2,
				storyActionScrollHeight = storyOffsetBottom - storyActionMargin;

			// Test if window scroll from top is greater or equal to that of the bottom of the story
			if (getWinScrollTop() >= storyActionScrollHeight) {
				// Test if element has the class, if not apply it and set the position of the element
				if (!storyActions.hasClass(storyActionsStuckClass)) {
					storyActions.addClass(storyActionsStuckClass).css({
						'top': storyActionScrollHeight
					});
				}
			}
			else {
				// Test if the element has the class, if so, remove it and clear styling
				if (storyActions.hasClass(storyActionsStuckClass)) {
					storyActions.removeClass(storyActionsStuckClass).attr('style', '');
				}
			}
		}
		else {
			// Remove class and styling for the element
			storyActions.removeClass(storyActionsStuckClass).attr('style', '');
		}
	}

}// END storyActionsScroll


/*
 * Define the position of the story action depending on the width of the browser window
 */
function storyActionPosition() {

	if (story.length) {
		var bodyClass = 'story-actions--body',
			headerClass = 'story-actions--header';

		// Test if browser size
		if (winWidth >= config.storyActionPositionChange) {
			if (!storyActions.hasClass(bodyClass))
				storyActions.removeClass(headerClass).addClass(bodyClass).detach().appendTo(body);
		}
		else {
			if (!storyActions.hasClass(headerClass))
				storyActions.removeClass(bodyClass).addClass(headerClass).detach().appendTo('.story-hdr .inner');
		}
	}

}// END storyActionPosition


/*
 * Show story actions once user scrolls to start of story
 */
function showStoryActions() {

	var storyStart = $('.story-container h1').offset().top;

	if (getWinScrollTop() >= storyStart) {
		$('.story-actions').addClass('story-actions--show');
	}
	else {
		$('.story-actions').removeClass('story-actions--show');
	}

}// END showStoryActions


/*
 * Story vote action
 */
function voteClick(element) {

	if (!element.hasClass('login-required')) {
		var votedClass = 'story-action-voted',
			actionData = {
				type: element.data('type'),
				id: element.data('id')
			},
			voted = element.hasClass(votedClass),
			action = voted ? 'remove' : 'add',
			url = '/actions/businessLogic/votes/' + action;

		actionData[window.csrfTokenName] = window.csrfTokenValue; // Append CSRF Token

		// Perform AJAX request to update user field
		// Set headers, otherwise it won't work
		$.ajax({
			headers: {
		        'Content-Type':'application/x-www-form-urlencoded'
		    },
			method: 'POST',
			url: url,
			dataType: 'json',
			data: actionData,

			beforeSend: function() {
				// Toggle the voted class after testing the current state of the element
				element.toggleClass(votedClass);
		    }
		})
	    .done(function(data) {
			log('Story ' + action + ' `' + actionData.type + '` action success : ', data);

			if (actionData.type == 'like') {
				var hideClass = 'like-count--hide',
					likeCount = $('.like-count'),
					tally = likeCount.data('tally'),
					newTally = voted ? tally - 1 : tally + 1;

				if (newTally > 0 && likeCount.hasClass(hideClass)) {
					likeCount.removeClass(hideClass);
				}

				if (newTally < 1 && !likeCount.hasClass(hideClass)) {
					likeCount.addClass(hideClass);
				}

				// Only update the value if it is under 1000, else just change the data tag
				// Any value over 999 will have number prefixing, we don't want to override that
				if (newTally <= 999) {
					likeCount.text(newTally).data('tally', newTally);
				}
				else {
					likeCount.data('tally', newTally);
				}
			}
			else if (actionData.type == 'bookmark') {
				if (voted) {
					// Remove class
					element.removeClass('icon-bookmarked');
				}
				else {
					// Add class
					element.addClass('icon-bookmarked');
				}
			}
	    })
	    .fail(function(data) {
			console.log('Story ' + action + ' `' + actionData.type + '` action error');
			log(data);

			// Error changing vote - reset button
			element.toggleClass(votedClass);
		});
	}

}// END voteClick


/*
 * Show modal
 *
 * @param [{string}] modal [The modal to open]
 */
function showModal(modal) {

	$('body').addClass('no-scroll');
	$('#' + modal + '-modal').addClass('showee');

}// END showModal


/*
 * Close modal
 */
function closeModal() {

	var modal = $('.modal.showee');

	modal.removeClass('showee');
	$('body').removeClass('no-scroll');

	if (modal.is('#write-instructions-modal')) {
		updateMemberWriteInstructions();
	}

}// END closeModal


/*
 * Setup related slider
 */
function setRelatedSlider() {

    var relatedSliderItems = $('.related-slider-wpr .story-excerpt'),
        relatedSliderItemsLength = relatedSliderItems.length,
        relatedSliderItemsWidth = relatedSliderItems.outerWidth(true),
        relatedSliderWidth = (relatedSliderItemsLength * relatedSliderItemsWidth) + 2; // Add 2 px for Safari fix

    if ($('.related-slider-frm').hasClass('fade-in')) {
        $('.related-slider-frm').removeClass('fade-in');
	}

    $('.related-slider-wpr').css({
        width: relatedSliderWidth
    });

    $('.related-slider-frm').addClass('fade-in');

}// END setRelatedSlider


/*
 * Show error message
 *
 * @param [{string}] error [The error message]
 */
function showErrorMessage(error) {

	return '<div class="message message-small message-error"><p>' + error + '</p></div>';

}// END showErrorMessage


/*
 * Check URL segment
 *
 * @param part [int] - the segment number to check
 * @param text [string] - the text to check against
 */
function checkUrlSegment(part, text) {

	var pathArray = window.location.pathname.split('/'),
		section = pathArray[part],
		check = section == text ? true : false;

	return check;

}// END checkUrlSegment


/*
 * Get text
 * Get the highlight text
 */
function getText() {

	var sel;

	if( window.getSelection ){
		sel = window.getSelection();
	}
	else if( document.getSelection ){
		sel = document.getSelection();
	}
	else if( document.selection ){
		sel = document.selection.createRange();
	}

	//document.selection && document.selection.type != "Control"

	return sel;

}// END getText


/*
 * Get text
 * Get the highlight text
 */
function getSelectionText() {

    var text = '';

    if( window.getSelection ) {
        text = window.getSelection().toString();
    }
    else if( document.selection && document.selection.type != "Control" ) {
        text = document.selection.createRange().text;
    }

    return text;

}// END getSelectionText


/*
 * Tweet length
 */
function tweetLength(tweet_met) {

	var tweet_char = 280, // Tweet character length
		remove_spaces = 5; // Remove the characters or spaces used elsewhere in the URL - in this case the '...' and and quotes

	return tweet_char - remove_spaces - tweet_met.length;

}// END tweetLength


/*
 * Trunchcate tweet
 */
function trunchTweet(string, limit) {

   return string = string.length > limit ? string.substring(0,limit) + "...": string;

}// END trunchTweet


/*
 * Save story
 *
 * @param [{string}] title [The title value of the write story form]
 */
function clearWriteTitle(title) {

	if (title == '')
		writeStoryTitle.removeClass('remove-placeholder');
	else
		writeStoryTitle.addClass('remove-placeholder');

}// END clearWriteTitle


/*
 * Show saved story message
 */
function storySaveMessage(message) {

	var savedClass = '';

	if (message == 'saved') {
		var currentdate = new Date();
		message = 'Last saved : ' + currentdate.getHours() + ':' + currentdate.getMinutes();
	}
	else if (message == 'error'){
		message = 'Error saving';
	}


	if (!$('.save-message').length) {
		$('body').append('<div class="save-message" style="display: none;">' + message +'</div>')
		$('.save-message').fadeIn(200);
	}
	else {
		$('.save-message').text(message);
	}

}// END storySavedMessage


/*
 * Save story
 *
 * @param [{string}] status [The status to save the entry to]
 */
function saveStory(status) {

	var errors = 0;

	// Validate title
	if (writeStoryTitle.text() == '') {
		errors++;
	}
	else {

	}

	// Validate story
	if (writeStoryText.val() == '') {
		errors++;
	}
	else {

	}

	// If status is live, then check to see if all fields are completed
	if (status == 'live'){
		// Categories
		if (!$('.story-cat').is(':checked')) {
			console.log('No categories');
			errors++;
		}
		else {

		}

		// Age rating
		if ($('#write-age-rating').val() == '') {
			console.log('No age rating');
			errors++;
		}
		else {

		}

		// Excerpt
		if ($('#write-story-excerpt').val() == '') {
			console.log('No excerpt');
			errors++;
		}
		else {

		}
	}// END if


	// Check for errors
	if (errors) {
		console.log('Write form errors');
	}
	else {
		submitStory(status);
	}

}// END saveStory


/*
 * Submit story
 *
 * @param [{string}] status [The status to save the entry to]
 */
function submitStory(status) {

	var form = $('#write-form')[0], // You need to use standard javascript object here
		data = new FormData(form);

	if (writeEntryId) {
		data.append('entryId', writeEntryId);
	}

	if (status == 'live') {
		// Set entry as live
		data.append('enabled', '1');
	}
	else {
		// Set entry as draft
		data.append('enabled', '0');
	}

	// Pass entry data to form which is not from native inputs
	data.append('title', writeStoryTitle.text());

	// Test output of form data
	// for (var pair of data.entries()) {
	// 	console.log(pair[0]+ '= '+ pair[1]);
	// }

	// Submit
	$.ajax({
		method: 'POST',
		url: '/',
		data: data,
		// THIS MUST BE DONE FOR FILE UPLOADING
    	contentType: false,
    	processData: false,

		beforeSend: function() {
	        storySaveMessage('Saving...');
	    }
	})
    .done(function(data) {
		log('Write form success : ', data);

		if (data.success === true){
			storySaveMessage('saved');
			writeEntryId = data.id;

			// Update URL - to include username and story title - so if user refreshes page it goes to the correct URL
			//window.history.replaceState('', document.title, window.location.href + '');

			// TODO: if publish via AJAX, update status badge
			if (status == 'live'){
				// Change status badge to live
				if ($('.story-status-badge span').text() != 'Live')
					$('.story-status-badge span').text('Live');

				// Update status variable
				saveStatus = 'live';
			}
			else {
				// Change status to draft
				if ($('.story-status-badge span').text() != 'Draft')
					$('.story-status-badge span').text('Draft');

				// Update status variable
				saveStatus = 'draft';
			}

			if ($('.story-status-badge').hasClass('hidden-el'))
				$('.story-status-badge').removeClass('hidden-el')
		}
		else {
			storySaveMessage('error');
		}
    })
    .fail(function(data) {
		log('Write form error : ', data);
		storySaveMessage('error');
	});

}// END submitStory


/**
 * Delete story
 *
 * @param [{string}] button [The delete button item]
 */
function deleteStory(button) {

	var confirmDelete = confirm('Are you sure you want to delete this story?');

	if (confirmDelete == true) {
		log('Confirmed deletion');

		var entryId = button.data('entry'),
			data = {
	        	entryId: entryId
	    	};

		data[window.csrfTokenName] = window.csrfTokenValue; // Append CSRF Token

	    $.ajax({
	        context: this,
	        type: 'POST',
	        dataType: 'json',
	        data: data,
	        url: '/actions/entries/deleteEntry'
		})
       	.done(function(data){
            log('Delete story success : ', data);

			if (data.success === true){
				button.parent().remove();
			}
        })
	    .fail(function(data) {
			console.log('Delete story error : ', data);
	    });
	}

}// END deleteStory


/*
 * Calculate read time
 */
function calcReadTime(count) {

	var readTime = '',
		readSpeed = config.readSpeed,
		fractionTime = count / readSpeed;

	// Return if less than 1 minute
	if( fractionTime < 1 ) {
		readTime = '< 1 min';
	}
	else {
		// Round fraction up
		var resultTime = Math.ceil(count / readSpeed),
			minTxt = resultTime == 1 ? 'min' : 'mins';

		readTime = resultTime + ' ' + minTxt;
	}

	return readTime;

}// END calcReadTime


/**
 * Update member profile - Write instructions
 */
function updateMemberWriteInstructions() {

	log('Update profile with setting!');

	// Put form data together
	var form = document.getElementById('member-write-instructions-form'),
		formData = new FormData(form);

	$.ajax({
		url: '/',
		type: 'POST',
		dataType: 'JSON',
		data: formData,
		// THIS MUST BE DONE FOR FILE UPLOADING
		processData: false,
		contentType: false
	})
	.done(function(data, status) {
		if (status == 'success' && data.success) {
			log('writeUpdate successful');
		}
		else {
			log('writeUpdate failed');
		}
	})
	.fail(function(data, status) {
		log('writeUpdate error!');
	});

}// END updateMemberWriteInstructions
