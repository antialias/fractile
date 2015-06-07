Precursor to [fractio](https://github.com/antialias/fractio). The code is a trainwreck, but it does demonstrate an interesting idea. Everything is in `dist_mandel.php`. Play with it, but please don't judge me based on this.

===

This is a distributed computing experiment that uses Javascript on the client side to render tiles of the Mandelbrot set. The server-side (currently in PHP) receives requests for work, and dispatches work to available clients.

# Live Demonstration #
[Here is a live demonstration](http://thomashallock.com/mandeljobs/) of this project in action:

Please understand that your browser may immediately start rendering requests from other browsers when you click the link above, which may slow down the overall performance of your computer while the page is open.


# History #
This was started as a proof of concept hack [entry for Yahoo!'s NYC Open Hack Day 2009](http://thomashallock.com/blog/2009/10/a-proof-of-concept-html-js-based-distributed-computing-platform/).
