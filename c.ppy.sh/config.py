import os
import configparser

class config:
	config = configparser.ConfigParser()
	fileName = "";		# config filename
	default = True      # if true, we have generated a default config.ini

	# Check if config.ini exists and load/generate it
	def __init__(self, __file):
		"""Initialize a config object

		__file -- filename"""

		self.fileName = __file
		if (os.path.isfile(self.fileName)):
			# config.ini found, load it
			self.config.read(self.fileName)
			self.default = False
		else:
			# config.ini not found, generate a default one
			self.generateDefaultConfig()
			self.default = True


	# Check if config.ini has all needed the keys
	def checkConfig(self):
		"""Check if this config has the required keys

		return -- True if valid, False if not"""

		try:
			# Try to get all the required keys
			self.config.get("db","host")
			self.config.get("db","username")
			self.config.get("db","password")
			self.config.get("db","database")

			self.config.get("server","server")
			self.config.get("server","host")
			self.config.get("server","port")
			self.config.get("server","outputpackets")

			# TODO: Flask config if server = flask
			self.config.get("flask","threaded")
			self.config.get("flask","debug")
			self.config.get("flask","logger")
			return True
		except:
			return False


	# Generate a default config.ini
	def generateDefaultConfig(self):
		"""Open and set default keys for that confg files"""

		# Open config.ini in write mode
		f = open(self.fileName, "w")

		# Set keys to config object
		self.config.add_section("db")
		self.config.set("db", "host", "localhost")
		self.config.set("db", "username", "root")
		self.config.set("db", "password", "")
		self.config.set("db", "database", "ripple")

		self.config.add_section("server")
		self.config.set("server", "server", "tornado")
		self.config.set("server", "host", "0.0.0.0")
		self.config.set("server", "port", "5001")
		self.config.set("server", "outputpackets", "0")

		self.config.add_section("flask")
		self.config.set("flask", "threaded", "1")
		self.config.set("flask", "debug", "0")
		self.config.set("flask", "logger", "0")

		# Write ini to file and close
		self.config.write(f)
		f.close()
