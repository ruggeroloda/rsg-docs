#!/usr/bin/env python3
"""About page (/about-2/, page 116) update, 15 Nov 2026: "How we review" section (#how-we-review, the byline's
"How we review" target) + current numbers + encoded contact email. usage: update.py IN OUT  (live or staging content)."""
import sys
s = open(sys.argv[1], encoding="utf-8").read()

def enc(t):  # HTML character references: shown normally, skipped by simple email scrapers
    return "".join("&#%d;" % ord(c) for c in t)
EMAIL = enc("guru@runningshoesguru.com")
MAILTO = '<a href="' + enc("mailto:") + EMAIL + '">' + EMAIL + '</a>'

HOW = '''<h2 id="how-we-review">How we review</h2>

<strong>We buy every shoe ourselves.</strong> Every pair is bought at retail with our own money, and each review names the shop it came from. We stopped accepting free samples in 2017. No brand pays for a review, sees it before it's published, or has any say in the score.

<strong>Real runners, real miles.</strong> Each shoe is tested by one of our runners in their own training: easy runs, long runs and faster sessions, on the surfaces the shoe is made for. We run at least 50 miles in a shoe before we write about it, often much more. The writer's name, running background and profiles are at the top of every review.

<strong>How we score.</strong> The expert score (out of 10) is the tester's overall verdict. Under it, we rate 10 things separately: comfort, build quality, upper, sole unit, landing, transition, toe-off, traction, durability and value for money. Weight and heel drop are the brand's own figures: our testers wear different sizes, so the manufacturer's numbers are the standard everyone can compare. The user score is separate: it's the average rating from readers who own the shoe.

<strong>Prices and links.</strong> Prices come from several shops and are checked several times a day. If you buy through our links we may earn a commission, at no cost to you. It never affects a score, and shops are always listed cheapest first, whichever one pays us.

<strong>Dates you can trust.</strong> "Published" is when the review first went up. "Updated" changes only when we actually change the review's text.

<strong>Spotted a mistake?</strong> Email us at ''' + MAILTO + ''' and we'll fix it.

'''

def rep(old, new):
    global s
    assert s.count(old) == 1, ("not found once", old[:60])
    s = s.replace(old, new)

rep('<h2 id="awards">Awards</h2>', HOW + '<h2 id="awards">Awards</h2>')
rep('''<li>750,000 -> the number of visitors to our website every month</li>
<li>90,000 -> the number of runners on our mailing list</li>
<li>120 -> the number of running shoes we review every year</li>
<li>15 -> the number of our testers</li>''', '''<li>200,000 -> the number of visitors to our website every month</li>
<li>35,000 -> the number of runners on our mailing list</li>
<li>All the most important running shoe releases, reviewed every year</li>
<li>A team of experienced testers</li>''')
rep("My name is Ruggero, I recently passed 40 and while", "My name is Ruggero, I'm a few years away from 50 and while")
rep("So the team grew from me alone to what is now a team more than 15 testers.", "So the team grew from me alone to a team of testers.")
rep("We test more than 120 shoes a year !!!", "We review all the most important shoe releases every year.")
rep("Running Shoes Guru receives in excess of 25,000 visits every day from runners or runners-to-be all around the world.",
    "Running Shoes Guru receives around 200,000 visits every month from runners or runners-to-be all around the world.")
rep('<a href="mailto:guru@www.runningshoesguru.com">guru@www.runningshoesguru.com</a><br />',
    MAILTO + ' or use our <a href="/contact-us/">contact form</a>.<br />')
assert "guru@" not in s, "plain email left"
open(sys.argv[2], "w", encoding="utf-8").write(s)
print("ok", len(s))
