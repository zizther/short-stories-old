/* --------------------------------------------------------------
	Variables
-------------------------------------------------------------- */
var winScrollTop,
    win,
    winHeight,
    winWidth,
	body,
    mnHdr,
    keywordForm,
    keywordInput,
    progressBar,
    progressWidth,
    storiesListGrid,
	story,
	storyPara,
    storyMax,
    storyHdr,
    storyHdrHeight,
    storyActions,
    storyActionsSticky,
    storyOffsetBottom,
    writeStory,
    writeForm,
    writeEntryId,
    writeStoryTitle,
    writeStoryText,
    writeSaveMessage,
    searchBoxShow = false,

	// Many browsers include different browser names in their UA.
	// This uses negative look-arounds and it excludes Chrome, Edge,
	// and all Android browsers that include the Safari name in their user agent.
	isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);


/* --------------------------------------------------------------
	Document Ready
-------------------------------------------------------------- */
$(document).ready(function(){

    /*** Variables ***/
    win = $(window);
    body = $('body');

    mnHdr = $('.mn-hdr');
    keywordForm = $('.keyword-search');
    keywordInput = $('.keyword-search input');
    progressBar = $('.progress-bar');
    storiesListGrid = $('.stories-list .stories-grid');
    storyContainer = $('.story-container');
    story = $('.story-content');
    storyHdr = $('.story-hdr');
    storyPara = $('.story-content p');
    storyActions = $('.story-actions');
    writeStory = $('.write-story');
    writeStoryTitle = $('#write-story-title');
    writeForm = $('#write-form');
    writeStoryText = $('.redactor-editor');

    resizeVars();


    /*** Window Scroll ***/
    win.scroll(scrollHandler);

    /*** Window resize ***/
    win.resize(resize);

    // Stop Janky scroll on touch device scrolling
    // https://developers.google.com/web/tools/lighthouse/audits/passive-event-listeners
    function onTouchStart() {}
    // Use our detect's results. passive applied if supported, capture will be false either way.
    document.addEventListener('touchstart', onTouchStart, supportsPassive ? { passive: true } : false);


    // Key up
    $(document).keyup(function(e) {
        if (e.key === "Escape") { // escape key maps to keycode `27`
            if (searchBoxShow) {
                searchBox('close');
            }
        }
    });




    /*** Mobile Navigation ***/
    $('.mobile-nav-btn').click(function(){
        if (body.hasClass('nav-open')) {
	        body.removeClass('nav-open');
		}
	    else {
	        body.addClass('nav-open');
		}
    });

    /*** Main Nav dropdown ***/
    function showNav(){
        $(this).find('.dd-nav').fadeIn(200);
    }
    function hideNav(){
        $(this).find('.dd-nav').fadeOut(200);
    }

    $('.mn-nav .dd').hoverIntent({
        over: showNav,
        out: hideNav
    });

    $('.mn-nav .dd-btn, .mobile-nav .dd-btn').click(function(e){
        e.preventDefault();
    });

    /*** Scroll to top ***/
    $('.scroll-top').click(function(e){
        e.preventDefault();

		scrollTo(0);
	});

	/*** Story share ***/
	function showStoryShare(){
        $(this).find('.story-share-options').fadeIn(200);
    }
    function hideStoryShare(){
        $(this).find('.story-share-options').fadeOut(200);
    }
    $('.story-share').hoverIntent({
        over: showStoryShare,
        out: hideStoryShare,
        timeout: 300
    });

    /*** Maintenance message ***/
    $('.message-maintenance .close-btn').click(function(){
		$('.message-maintenance').remove();

		// Set cookie
		Cookies.set('_maintenance', '1', { expires: 1 });
    });

    /*** Profile dropdown menu ***/
    function showProfileDd(){
        $(this).find('.my-account-dd').fadeIn(200);
    }
    function hideProfileDd(){
        $(this).find('.my-account-dd').fadeOut(200);
    }

    $('.my-account-btn').hoverIntent({
        over: showProfileDd,
        out: hideProfileDd,
        timeout: 300
    });

    // Close modal
    $('.modal-close').on('click', function(){
        closeModal($(this).parent());
    });

    $('.modal-general').on('click', function() {
        closeModal($(this));
    });
    $('.modal-general .modal-content').on('click', function(e){
        e.stopPropagation();
    });

    // Cookie button
    $('.cookie-continue').click(function(e){
        e.preventDefault();

        $('.cookie-frame').remove();
    });

	// Search button
	$('.search-button').click(function(e){
		e.preventDefault();

        searchBox('open');
	});
	$('.search-box__close').click(function(e){
		e.preventDefault();

        searchBox('close');
	});




    // Login button
    $('.login-btn').on('click', function(e){
	    e.preventDefault();

		showModal('login');
    });

    // Not logged in button - Show CTA modal
    $('.login-required').on('click', function(e){
        e.preventDefault();

        showModal('member-cta');
    });

    /*** Tabbed nav ***/
	if (getUrlParameter('tab')) {
		// Scroll to tabbed section
		scrollTo($('.tabbed-section').offset().top);
	}

    $('.tab-nav a').click(function(e) {
        e.preventDefault();

        var sec = $(this).attr('href'),
            nav = $(this).parent(),
            secParent = $(this).parent().parent(),
            secEl = secParent.find('.tab' + sec);

        // Swap active buttons
        nav.find('.active').removeClass('active');
        $(this).addClass('active');

        // Swap active sections
        secParent.find('.tab.active').removeClass('active');
        secEl.addClass('active');

		// Scroll to tabbed section
		scrollTo($('.tabbed-section').offset().top - 50);

        // Test if history is supported
        if (window.history && window.history.replaceState) {
            history.replaceState(null, null, '?tab='+sec.replace('#', ''));
        }
    });

    // Character count for member bio
    $('#member-bio').simplyCountable({
         maxCount: config.memberBioCount
    });


    /*** Excerpt hover ***/
    $('.story-excerpt h2 a').hover(function(){
        $(this).closest('.story-excerpt').addClass('excerpt--hover');
    },
    function(){
        $(this).closest('.story-excerpt').removeClass('excerpt--hover');
    });

    /*** Delete story ***/
    $('.delete-story').click(function(e){
        e.preventDefault();

        deleteStory($(this));
    });

    $('.write-delete-story').click(function(e){
        e.preventDefault();

        var confirmDelete = confirm('Are you sure you want to delete this story?');

    	if (confirmDelete == true) {
    		log('Confirmed deletion');

    		var entryId = $(this).data('entry'),
    			data = {
    	        	entryId: entryId
    	    	};

    	    $.ajax({
    	        context: this,
    	        type: 'POST',
    	        dataType: 'json',
    	        data: data,
    	        url: '/actions/entries/deleteEntry',
    	        success: function(data){
    	            log('Delete story success : ', data);

    				if (data.success === true) {
    					window.location.href = $(this).data('redirect');
    				}
    	        },
    		    error: function(data) {
    				console.log('Delete story error : ', data);
    		    }
    	    });
    	}
    });


    /**
     * Write instructions modal
     * If member has not seen write instructions before then show the modal
     */
    if (typeof showWriteInstructions !== 'undefined') {
        setTimeout(function() {
            showModal('write-instructions');

            $('.write-instructions-got-it').click(function(){
                closeModal();
            });
        }, 500);
    }// END if

    /*** Login form ***/
    $('#login-form').submit(function(e) {
        e.preventDefault();

        $('.message-error').remove();

        $('.login-form-submit').addClass('button-disabled');

        var data = $(this).serialize();

        $.ajax({
            method: 'POST',
            url: '/',
            data: data,
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            beforeSend: function() {
    	        $('.login-form-submit').addClass('button-loader');
    	    },
    	    success: function(data) {
                if (data.success == true){
                    window.location.href = data.returnUrl;
                }

                if (data.error){
                    $('.login-form-submit').before(showErrorMessage(data.error));
                    $('.login-form-submit').removeClass('button-loader button-disabled');
                }
            },
            error: function(data) {
                console.log('error', data);
                $('.login-form-submit').removeClass('button-loader button-disabled');
    	    }
        });
    });


	/*** Story ***/
    if (story.length){

		storyActionPosition();

        storyHdrHeight = storyHdr.outerHeight();


        // Add ID to each element within story
        story.find('p, blockquote').each(function(index) {
            $(this).attr('id', index);
        });

        // Update bookmark
        var waypoints = story.find('p').waypoint(function(direction) {
            // TODO: Save bookmark on users account
            // With ID of element at the top of the screen
            //console.log('p#'+this.element.id);

            //if (direction === 'down') {}
        });






	    /*** Tweet highlighted copy ***/
        // Test for this at the start so the variable can put it in the DOM
        body.append('<a class="tweet-this" href="#0" title=""><i class="icon-twitter"></i> Tweet</a>');

        var storyCopy = storyPara,
            tweetPop = $('.tweet-this'),
            startPosX,
    		startPosY,
    		selection,
    		hightlight = false;

    	storyCopy.on('mousedown', function(e){
    		startPosX = e.pageX;
    		startPosY = e.pageY;
    	});

    	// When text is highlighted in the sotry content
    	storyCopy.on('mouseup', function(e){
            selection = getText().toString(); // Make sure to convert this to a string so we can play around with it

            // Test to see if the highlighted string is empty or not
            if (selection !== ''){

            	hightlight = true;

            	if( e.pageY < startPosY ){
    	        	tweetPop.css({ left: e.pageX, top: e.pageY-65 }).addClass('active');
            	}
            	else {
    	        	tweetPop.css({ left: startPosX, top: startPosY-65 }).addClass('active');
            	}
    		}
    		else {
    	        tweetPop.removeClass('active');
            }
        });

        // Bind event to close tweet button
        body.on('click', function(e){
        	if (hightlight) {
    	    	tweetPop.removeClass('active');
    	    	hightlight = false;
    	    }
        });

        storyCopy.on('click', function(e){
        	e.stopPropagation(); // Remove the click bind
        });

    	// Click event for the tweet button
    	tweetPop.on('click', function(event) {
    		event.stopPropagation(); // Remove the click bind from body on this button

    		var tweet_text = selection,
    			tweet_meta = ' — ' + storyAuthorName,
                storyUrl = encodeURI(window.location),
    			tweet_meta_length = tweetLength(tweet_meta),
    			tweet_url,
    			width  = 575,
    			height = 400,
    			left   = ($(window).width()  - width)  / 2,
    			top    = ($(window).height() - height) / 2,
    			opts   = 'status=1' + ',width=' + width + ',height=' + height + ',top=' + top + ',left=' + left;


    		if (tweet_text.length > tweet_meta_length){
    			tweet_text = trunchTweet(tweet_text, tweet_meta_length);
    		}

    		tweet_text = encodeURI('“' + tweet_text + '”' + tweet_meta); // Construct full URL and encode it


    		tweet_url = 'http://twitter.com/share?text=' + tweet_text + '&url=' + storyUrl;

    		console.log(tweet_url);

    		setTimeout(function(){
    			window.open(tweet_url, 'twitter', opts);
    		}, 100);

    		return false;
    	});
    }// END if

	// Story votes
	$('.story-like, .story-bookmark').click(function(e){
		e.preventDefault();

		voteClick($(this));
	});


    // Bind an action to the deleteUserPhotoAction click event
    $('#delete-user-image').on('click', function(e) {
        e.preventDefault();

        // Add the value 'zap' to the delete user image input
        $('#delete-user-image-input').val('zap');
    });

    /*** Write story ***/
    if (writeStory.length) {
        clearWriteTitle(writeStoryTitle.text());

        // Remove title placeholder
        writeStoryTitle.on('keydown, keypress', function(e) {
            writeStoryTitle.addClass('remove-placeholder');
        });
        writeStoryTitle.on('keyup', function(e) {
            clearWriteTitle($(this).text());
        });

        // Character count for excerpt
        $('#write-story-excerpt').simplyCountable({
             maxCount: config.storyExcerptCount
        });

        // Save write form - will submit with AJAX
        $('.save-story-button').click(function(e) {
    		e.preventDefault();

    		var status = $(this).data('status');
    		saveStory(status);
    	});

        // Keydown on page
        $(document).keydown(function(e) {
            // If Control or Command key is pressed and the S key is pressed
            // run save function. 83 is the key code for S.
            if ((event.ctrlKey || event.metaKey) && event.which == 83) {
                // Save Function
                e.preventDefault();

                // Save story - use global saveStatus variable
                saveStory(saveStatus);
            };
        });

        // Stop form submitting
        writeForm.submit(function(e){
            e.preventDefault();
        });
    }// END if

});


/* --------------------------------------------------------------
	Window Load
-------------------------------------------------------------- */
$(window).on('load', function(e) {

    if (story.length) {
    	// Variables
        storyMax = getStoryMax();

        storyStickyHdr();
        setRelatedSlider();

		// If story actions in sidebar, then use timeout due to font loading and DOM adjusting.
		// Show story actions once the position is correct
		if (winWidth >= config.storyActionPositionChange) {
			setTimeout(function(){
				storyActionsScroll();
				showStoryActions();
			}, 200);
		}

		win.scroll(function(){
			setTimeout(function(){
				storyActionsScroll();
				showStoryActions();
			}, 200);
		});

        if (storyMax > 0) {
            setProgressWidth();

            $(document).on('scroll', function(){
                getWinScrollTop() >= story.offset().top ? setProgressWidth() : progressBar.css({ width: 0 });
            });
        }

        $('.related-slider-frm').on('mouseenter', function() {
            $(this).addClass('hide-grad');
        });
        $('.related-slider-frm').on('mouseleave', function() {
            $(this).removeClass('hide-grad');
        });
    }// END if

});
